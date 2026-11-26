<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientPaymentAdminController extends Controller
{
    private function authorizePayment(Request $request, string $action, ?int $projectId = null): void
    {
        $user = $request->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('client_control.payments.'.$action), 403);
        if ($projectId !== null) {
            abort_unless($user->hasProjectAccess($projectId), 403);
        }
    }

    private function project(int $id): object
    {
        $project = DB::table('projects')->where('id', $id)->first();
        abort_unless($project, 404);
        return $project;
    }

    private function contract(int $projectId): object
    {
        $contract = DB::table('client_payment_contracts')->where('project_id', $projectId)->first();
        abort_unless($contract, 404);
        return $contract;
    }

    private function event(Request $request, int $contractId, string $action, ?int $milestoneId, ?array $before, ?array $after): void
    {
        DB::table('client_payment_events')->insert([
            'contract_id' => $contractId, 'milestone_id' => $milestoneId,
            'actor_guard' => 'web', 'actor_id' => $request->user()->id,
            'action' => $action, 'before_values' => $before ? json_encode($before) : null,
            'after_values' => $after ? json_encode($after) : null,
            'ip_address' => $request->ip(), 'created_at' => now(),
        ]);
    }

    private function editable(object $contract): void
    {
        // Client Control is a manually maintained information module; publication does not lock editing.
        abort_unless($contract->status !== 'closed', 409, 'This contract is closed.');
    }

    private function validateStage(int $projectId, ?int $stageId): void
    {
        if ($stageId !== null && !DB::table('client_work_stages')->where('id', $stageId)->where('project_id', $projectId)->where('status', 'active')->exists()) {
            throw ValidationException::withMessages(['work_stage_id' => 'Choose an active stage belonging to this project.']);
        }
    }

    public function index(Request $request)
    {
        $this->authorizePayment($request, 'view');
        $user = $request->user();
        $projects = DB::table('projects')->select('id', 'project_code', 'project_name', 'status')->orderBy('project_name')->get()
            ->filter(fn ($project) => $user->hasProjectAccess((int) $project->id));
        $contracts = DB::table('client_payment_contracts')->whereIn('project_id', $projects->pluck('id')->all())->get()->keyBy('project_id');
        return view('client-control.admin.payments.index', compact('projects', 'contracts'));
    }

    public function show(Request $request, int $project)
    {
        $this->authorizePayment($request, 'view', $project);
        $projectRecord = $this->project($project);
        $contract = DB::table('client_payment_contracts')->where('project_id', $project)->first();
        $milestones = $contract ? DB::table('client_payment_milestones')->where('contract_id', $contract->id)->orderBy('sort_order')->orderBy('id')->get() : collect();
        $paymentStageMasters = DB::table('client_payment_stage_masters')->where('is_active', 1)->orderBy('sort_order')->get();
        $stages = DB::table('client_work_stages')->where('project_id', $project)->where('status', 'active')->orderBy('sort_order')->get();
        $received = $contract ? (float) DB::table('client_payment_receipts')->where('contract_id', $contract->id)->where('status', 'verified')->sum('amount') : 0;
        $scheduled = $milestones->whereNull('archived_at')->sum('scheduled_amount');
        $receiptRows = $contract ? DB::table('client_payment_receipts')->where('contract_id', $contract->id)->orderByDesc('received_on')->orderByDesc('id')->get() : collect();
        $verifiedByMilestone = $receiptRows->where('status', 'verified')->whereNotNull('milestone_id')->groupBy('milestone_id')->map(fn ($rows) => $rows->sum('amount'));
        $pendingByMilestone = $receiptRows->where('status', 'draft')->whereNotNull('milestone_id')->groupBy('milestone_id')->map(fn ($rows) => $rows->sum('amount'));
        $canRecord = $request->user()->hasPermission('client_control.payments.receipts.record');
        $canVerify = $request->user()->hasPermission('client_control.payments.receipts.verify');
        return view('client-control.admin.payments.project', compact('projectRecord', 'contract', 'milestones', 'stages', 'received', 'scheduled', 'receiptRows', 'verifiedByMilestone', 'pendingByMilestone', 'canRecord', 'canVerify', 'paymentStageMasters'));
    }

    public function storeContract(Request $request, int $project)
    {
        $this->authorizePayment($request, 'manage', $project);
        $this->project($project);
        $data = $request->validate([
            'contract_reference' => ['nullable', 'string', 'max:100'],
            'agreement_date' => ['nullable', 'date'],
            'agreed_amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'approved_variations' => ['nullable', 'numeric', 'decimal:0,2'],
            'payment_terms' => ['nullable', 'string', 'max:10000'],
        ]);
        DB::transaction(function () use ($request, $project, $data) {
            $existing = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            if ($existing) {
                $this->editable($existing);
                if (round((float) $existing->agreed_amount * 100) !== round((float) $data['agreed_amount'] * 100)
                    && DB::table('client_payment_receipts')->where('contract_id', $existing->id)->exists()) {
                    throw ValidationException::withMessages(['agreed_amount' => 'Cannot change the original agreed value after receipts exist. Use a controlled contract revision.']);
                }
                $before = (array) $existing;
                DB::table('client_payment_contracts')->where('id', $existing->id)->update([
                    'contract_reference' => $data['contract_reference'] ?? null,
                    'agreement_date' => $data['agreement_date'] ?? null,
                    'agreed_amount' => $data['agreed_amount'],
                    'approved_variations' => $data['approved_variations'] ?? 0,
                    'payment_terms' => $data['payment_terms'] ?? null,
                    'updated_by' => $request->user()->id, 'updated_at' => now(),
                ]);
                $this->event($request, $existing->id, 'contract_updated', null, $before, $data);
            } else {
                $id = DB::table('client_payment_contracts')->insertGetId([
                    'project_id' => $project, 'contract_reference' => $data['contract_reference'] ?? null,
                    'agreement_date' => $data['agreement_date'] ?? null,
                    'currency' => 'INR', 'agreed_amount' => $data['agreed_amount'],
                    'approved_variations' => $data['approved_variations'] ?? 0,
                    'payment_terms' => $data['payment_terms'] ?? null,
                    'status' => 'draft', 'revision' => 1,
                    'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->event($request, $id, 'contract_created', null, null, $data);
            }
        });
        return redirect()->route('client-control.admin.payments.show', $project)->with('success', 'Contract saved.');
    }

    private function milestoneRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'milestone_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::in(['advance', 'construction', 'additional', 'final', 'retention', 'custom'])],
            'work_stage_id' => ['nullable', 'integer'],
            'scheduled_amount' => ['nullable', 'required_if:allocation_mode,amount', 'numeric', 'gte:0', 'decimal:0,2'],
            'allocation_mode' => ['nullable', Rule::in(['amount','percent'])],
            'scheduled_percent' => ['nullable', 'required_if:allocation_mode,percent', 'numeric', 'between:0,100', 'decimal:0,4'],
            'planned_due_date' => ['nullable', 'date'],
        ];
    }

    // Always use the ORIGINAL agreed value as the percentage denominator.
    // Approved variations are recorded as additional amount-only milestones.
    private function allocation(array $data, object $contract): array
    {
        $base = (float) $contract->agreed_amount;
        abort_if($base <= 0, 422, 'Original agreed contract amount must be positive.');
        $mode = $data['allocation_mode'] ?? 'amount';
        if (($data['type'] ?? '') === 'additional' && $mode === 'percent') {
            throw ValidationException::withMessages(['allocation_mode' => 'Additional variation milestones must use a fixed amount, not a percentage of the original contract.']);
        }
        $amount = $mode === 'percent'
            ? round($base * (float) ($data['scheduled_percent'] ?? 0) / 100, 2)
            : round((float) ($data['scheduled_amount'] ?? 0), 2);
        $percent = ($data['type'] ?? '') === 'additional' ? null : round($amount / $base * 100, 4);
        return ['scheduled_amount' => $amount, 'scheduled_percent' => $percent];
    }

    // Populate a new draft schedule from selected approved master stages. No values are invented.
    public function loadStageMaster(Request $request, int $project)
    {
        $this->authorizePayment($request, 'manage', $project);
        $data = $request->validate([
            'stage_ids' => ['required', 'array', 'min:1', 'max:50'],
            'stage_ids.*' => ['required', 'integer', 'distinct', Rule::exists('client_payment_stage_masters', 'id')->where('is_active', 1)],
        ]);
        DB::transaction(function () use ($request, $project, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            // Never silently append to, overwrite, or duplicate a live schedule.
            abort_if(DB::table('client_payment_milestones')->where('contract_id', $contract->id)->exists(), 409, 'This contract already has payment stage history. Add stages individually instead.');
            $masters = DB::table('client_payment_stage_masters')->where('is_active', 1)
                ->whereIn('id', $data['stage_ids'])->orderBy('sort_order')->lockForUpdate()->get();
            abort_unless($masters->count() === count($data['stage_ids']), 422);
            foreach ($masters as $index => $master) {
                $type = $master->stage_code === 'PAY-01' ? 'advance' : ($master->stage_code === 'PAY-14' ? 'final' : 'construction');
                $id = DB::table('client_payment_milestones')->insertGetId([
                    'contract_id' => $contract->id, 'payment_stage_master_id' => $master->id,
                    'milestone_code' => $master->stage_code, 'title' => $master->stage_name,
                    'type' => $type, 'scheduled_amount' => 0, 'scheduled_percent' => 0,
                    'status' => 'draft', 'sort_order' => $index + 1, 'revision' => $contract->revision,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->event($request, $contract->id, 'master_stage_loaded', $id, null, ['stage_code' => $master->stage_code, 'title' => $master->stage_name]);
            }
        });
        return back()->with('success', 'Selected payment stages loaded. Set each agreed amount before approval.');
    }

    public function storeMilestone(Request $request, int $project)
    {
        $this->authorizePayment($request, 'manage', $project);
        $data = $request->validate($this->milestoneRules());
        $this->validateStage($project, isset($data['work_stage_id']) ? (int) $data['work_stage_id'] : null);
        DB::transaction(function () use ($request, $project, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            $allocation = $this->allocation($data, $contract);
            $order = (int) DB::table('client_payment_milestones')->where('contract_id', $contract->id)->max('sort_order') + 1;
            $id = DB::table('client_payment_milestones')->insertGetId([
                'contract_id' => $contract->id, 'title' => $data['title'], 'milestone_code' => $data['milestone_code'] ?? null,
                'description' => $data['description'] ?? null, 'type' => $data['type'],
                'work_stage_id' => $data['work_stage_id'] ?? null, 'scheduled_amount' => $allocation['scheduled_amount'], 'scheduled_percent' => $allocation['scheduled_percent'],
                'planned_due_date' => $data['planned_due_date'] ?? null,
                'status' => 'draft', 'sort_order' => $order, 'revision' => $contract->revision,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->event($request, $contract->id, 'milestone_created', $id, null, $data);
        });
        return back()->with('success', 'Milestone added.');
    }

    public function updateMilestone(Request $request, int $project, int $milestone)
    {
        $this->authorizePayment($request, 'manage', $project);
        $data = $request->validate($this->milestoneRules());
        $this->validateStage($project, isset($data['work_stage_id']) ? (int) $data['work_stage_id'] : null);
        DB::transaction(function () use ($request, $project, $milestone, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            $old = DB::table('client_payment_milestones')->where('contract_id', $contract->id)->where('id', $milestone)->lockForUpdate()->first();
            abort_unless($old && !$old->archived_at, 404);
            $allocation = $this->allocation($data, $contract);
            $already = (float) DB::table('client_payment_receipts')->where('milestone_id', $milestone)->whereIn('status', ['draft', 'verified'])->sum('amount');
            if ($allocation['scheduled_amount'] + 0.001 < $already) {
                throw ValidationException::withMessages(['scheduled_amount' => 'Stage amount cannot be below recorded payments.']);
            }
            DB::table('client_payment_milestones')->where('id', $milestone)->update([
                'title' => $data['title'], 'milestone_code' => $data['milestone_code'] ?? null,
                'description' => $data['description'] ?? null, 'type' => $data['type'],
                'work_stage_id' => $data['work_stage_id'] ?? null, 'scheduled_amount' => $allocation['scheduled_amount'], 'scheduled_percent' => $allocation['scheduled_percent'],
                'planned_due_date' => $data['planned_due_date'] ?? null, 'updated_at' => now(),
            ]);
            $this->event($request, $contract->id, 'milestone_updated', $milestone, (array) $old, $data);
        });
        return back()->with('success', 'Milestone updated.');
    }

    public function archiveMilestone(Request $request, int $project, int $milestone)
    {
        $this->authorizePayment($request, 'manage', $project);
        DB::transaction(function () use ($request, $project, $milestone) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            $old = DB::table('client_payment_milestones')->where('contract_id', $contract->id)->where('id', $milestone)->lockForUpdate()->first();
            abort_unless($old && !$old->archived_at, 404);
            abort_if(DB::table('client_payment_receipts')->where('milestone_id', $milestone)->exists(), 409, 'Cannot archive a milestone with recorded receipts.');
            DB::table('client_payment_milestones')->where('id', $milestone)->update(['status' => 'archived', 'archived_at' => now(), 'updated_at' => now()]);
            $this->event($request, $contract->id, 'milestone_archived', $milestone, (array) $old, ['status' => 'archived']);
        });
        return back()->with('success', 'Milestone archived.');
    }

    public function moveMilestone(Request $request, int $project, int $milestone)
    {
        $this->authorizePayment($request, 'manage', $project);
        $data = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        DB::transaction(function () use ($request, $project, $milestone, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            $items = DB::table('client_payment_milestones')->where('contract_id', $contract->id)->whereNull('archived_at')->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $index = $items->search(fn ($item) => (int) $item->id === $milestone);
            abort_if($index === false, 404);
            $swap = $index + ($data['direction'] === 'up' ? -1 : 1);
            if ($swap < 0 || $swap >= $items->count()) return;
            $ids = $items->pluck('id')->all();
            [$ids[$index], $ids[$swap]] = [$ids[$swap], $ids[$index]];
            foreach ($ids as $i => $id) DB::table('client_payment_milestones')->where('id', $id)->update(['sort_order' => $i + 1]);
            $this->event($request, $contract->id, 'milestone_reordered', $milestone, null, ['direction' => $data['direction']]);
        });
        return back()->with('success', 'Milestone order updated.');
    }
    public function splitMilestone(Request $request, int $project, int $milestone)
    {
        $this->authorizePayment($request, 'manage', $project);
        $data = $request->validate([
            'first_title' => ['required','string','max:255'],
            'first_amount' => ['required','numeric','gt:0','decimal:0,2'],
            'second_title' => ['required','string','max:255'],
        ]);
        DB::transaction(function () use ($request, $project, $milestone, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            $old = DB::table('client_payment_milestones')->where('contract_id', $contract->id)->where('id', $milestone)->lockForUpdate()->first();
            abort_unless($old && !$old->archived_at, 404);
            abort_if(DB::table('client_payment_receipts')->where('milestone_id', $milestone)->exists(), 409, 'A milestone with receipts cannot be split.');
            $original = (int) round((float) $old->scheduled_amount * 100);
            $first = (int) round((float) $data['first_amount'] * 100);
            if ($first <= 0 || $first >= $original) {
                throw ValidationException::withMessages(['first_amount' => 'The first amount must be greater than zero and less than the original milestone amount.']);
            }
            $second = ($original - $first) / 100;
            DB::table('client_payment_milestones')->where('contract_id', $contract->id)->where('sort_order', '>', $old->sort_order)->increment('sort_order');
            DB::table('client_payment_milestones')->where('id', $old->id)->update([
                'title' => $data['first_title'], 'scheduled_amount' => $first / 100,
                'scheduled_percent' => null, 'updated_at' => now(),
            ]);
            $newId = DB::table('client_payment_milestones')->insertGetId([
                'contract_id' => $contract->id, 'title' => $data['second_title'],
                'type' => $old->type, 'description' => $old->description,
                'work_stage_id' => $old->work_stage_id, 'scheduled_amount' => $second,
                'planned_due_date' => $old->planned_due_date, 'status' => 'draft',
                'sort_order' => (int) $old->sort_order + 1, 'revision' => $contract->revision,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->event($request, $contract->id, 'milestone_split', $milestone, (array) $old, [
                'first_amount' => $first / 100, 'second_milestone_id' => $newId,
                'second_amount' => $second, 'first_title' => $data['first_title'],
                'second_title' => $data['second_title'],
            ]);
        });
        return back()->with('success', 'Milestone split into two schedule rows.');
    }

    public function storeReceipt(Request $request, int $project, int $milestone)
    {
        $this->authorizePayment($request, 'receipts.record', $project);
        $data = $request->validate([
            'received_on' => ['required','date','before_or_equal:today'],
            'amount' => ['required','numeric','gt:0','decimal:0,2'],
            'payment_method' => ['nullable','string','max:40'],
            'transaction_reference' => ['nullable','string','max:150'],
            'receipt_reference' => ['nullable','string','max:100'],
            'remarks' => ['nullable','string','max:2000'],
        ]);
        DB::transaction(function () use ($request, $project, $milestone, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $item = DB::table('client_payment_milestones')->where('id', $milestone)->where('contract_id', $contract->id)->first();
            abort_unless($item && !$item->archived_at, 404);
            $allocated = DB::table('client_payment_receipts')->where('milestone_id', $milestone)->whereIn('status', ['draft','verified'])->sum('amount');
            if (round(((float)$allocated + (float)$data['amount']) * 100) > round((float)$item->scheduled_amount * 100)) {
                throw ValidationException::withMessages(['amount' => 'Amount exceeds the remaining milestone balance including pending receipts.']);
            }
            $id = DB::table('client_payment_receipts')->insertGetId([
                'contract_id' => $contract->id, 'milestone_id' => $milestone,
                'receipt_reference' => $data['receipt_reference'] ?? null,
                'received_on' => $data['received_on'], 'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'remarks' => $data['remarks'] ?? null, 'status' => 'verified',
                'verified_by' => $request->user()->id, 'verified_at' => now(),
                'recorded_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('client_payment_events')->insert([
                'contract_id' => $contract->id, 'milestone_id' => $milestone, 'receipt_id' => $id,
                'actor_guard' => 'web', 'actor_id' => $request->user()->id, 'action' => 'client_payment_recorded',
                'after_values' => json_encode($data), 'ip_address' => $request->ip(), 'created_at' => now(),
            ]);
        });
        return back()->with('success', 'Client payment recorded. Publish the updated statement to make it visible to the client.');
    }

    public function verifyReceipt(Request $request, int $project, int $receipt)
    {
        $this->authorizePayment($request, 'receipts.verify', $project);
        DB::transaction(function () use ($request, $project, $receipt) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $row = DB::table('client_payment_receipts')->where('contract_id', $contract->id)->where('id', $receipt)->lockForUpdate()->first();
            abort_unless($row && $row->status === 'draft', 404);
            $item = DB::table('client_payment_milestones')->where('id', $row->milestone_id)->where('contract_id', $contract->id)->first();
            abort_unless($item && !$item->archived_at, 409);
            DB::table('client_payment_receipts')->where('id', $receipt)->update([
                'status' => 'verified', 'verified_by' => $request->user()->id,
                'verified_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('client_payment_events')->insert([
                'contract_id' => $contract->id, 'milestone_id' => $row->milestone_id,
                'receipt_id' => $receipt, 'actor_guard' => 'web', 'actor_id' => $request->user()->id,
                'action' => 'receipt_verified', 'before_values' => json_encode(['status' => 'draft']),
                'after_values' => json_encode(['status' => 'verified']),
                'ip_address' => $request->ip(), 'created_at' => now(),
            ]);
        });
        return back()->with('success', 'Receipt verified and included in received totals.');
    }

    // Save the complete editable schedule atomically. Existing receipts are immutable.
    public function saveInlineSchedule(Request $request, int $project)
    {
        $this->authorizePayment($request, 'manage', $project);
        $data = $request->validate([
            'contract_revision' => ['required', 'integer', 'min:1'],
            'rows' => ['required', 'array', 'min:1', 'max:300'],
            'rows.*.id' => ['required', 'integer', 'distinct'],
            'rows.*.title' => ['required', 'string', 'max:255'],
            'rows.*.scheduled_amount' => ['required', 'numeric', 'gte:0', 'decimal:0,2'],
            'rows.*.updated_at' => ['required', 'string'],
        ]);
        DB::transaction(function () use ($request, $project, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $this->editable($contract);
            if ((int) $contract->revision !== (int) $data['contract_revision']) {
                throw ValidationException::withMessages(['rows' => 'Contract revision changed. Refresh before editing.']);
            }
            $items = DB::table('client_payment_milestones')->where('contract_id', $contract->id)
                ->whereNull('archived_at')->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $submitted = collect($data['rows']);
            $actualIds = $items->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $submittedIds = $submitted->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            if ($actualIds !== $submittedIds) {
                throw ValidationException::withMessages(['rows' => 'Payment stages changed. Refresh and try again.']);
            }
            $byId = $items->keyBy('id');
            $baseCents = (int) round((float) $contract->agreed_amount * 100);
            $variationCents = (int) round((float) $contract->approved_variations * 100);
            $originalTotal = 0;
            $variationTotal = 0;
            foreach ($data['rows'] as $index => $row) {
                $old = $byId->get((int) $row['id']);
                if ((string) $old->updated_at !== (string) $row['updated_at']) {
                    throw ValidationException::withMessages(['rows' => 'A payment stage changed since the page loaded. Refresh before saving.']);
                }
                $amountCents = (int) round((float) $row['scheduled_amount'] * 100);
                if ($old->type === 'additional') $variationTotal += $amountCents;
                else $originalTotal += $amountCents;
                $changed = trim($old->title) !== trim($row['title']) ||
                    (int) round((float) $old->scheduled_amount * 100) !== $amountCents;
                if ($changed && DB::table('client_payment_receipts')->where('milestone_id', $old->id)->exists()) {
                    $paid = (int) round((float) DB::table('client_payment_receipts')->where('milestone_id', $old->id)->whereIn('status', ['draft', 'verified'])->sum('amount') * 100);
                    if ($amountCents < $paid) {
                        throw ValidationException::withMessages(['rows' => 'The amount for '.$old->title.' cannot be less than payments already recorded.']);
                    }
                }
            }
            // No mandatory 100% allocation or variation reconciliation in Client Control.
            foreach ($data['rows'] as $index => $row) {
                $old = $byId->get((int) $row['id']);
                $amountCents = (int) round((float) $row['scheduled_amount'] * 100);
                $title = trim($row['title']);
                $changed = $old->title !== $title || (int) round((float) $old->scheduled_amount * 100) !== $amountCents;
                $orderChanged = (int) $old->sort_order !== $index + 1;
                if (!$changed && !$orderChanged) continue;
                DB::table('client_payment_milestones')->where('id', $old->id)->update([
                    'title' => $title,
                    'scheduled_amount' => $amountCents / 100,
                    'scheduled_percent' => $old->type === 'additional' || $baseCents <= 0
                        ? null : round($amountCents / $baseCents * 100, 4),
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ]);
                $this->event($request, $contract->id, 'milestone_inline_updated', (int) $old->id,
                    ['title' => $old->title, 'scheduled_amount' => $old->scheduled_amount, 'sort_order' => $old->sort_order],
                    ['title' => $title, 'scheduled_amount' => $amountCents / 100, 'sort_order' => $index + 1]);
            }
        });
        return back()->with('success', 'Payment schedule changes saved.');
    }

    // Project-scoped receipts register: draft, verified and void entries remain visible.
    public function receiptsIndex(Request $request, int $project)
    {
        $this->authorizePayment($request, 'view', $project);
        $projectRecord = $this->project($project);
        $contract = DB::table('client_payment_contracts')->where('project_id', $project)->first();
        $receipts = $contract
            ? DB::table('client_payment_receipts as r')
                ->leftJoin('client_payment_milestones as m', 'm.id', '=', 'r.milestone_id')
                ->where('r.contract_id', $contract->id)
                ->select('r.*', 'm.title as milestone_title')
                ->orderByDesc('r.received_on')->orderByDesc('r.id')->paginate(30)
            : collect();
        $canVerify = $request->user()->hasPermission('client_control.payments.receipts.verify');
        return view('client-control.admin.payments.receipts', compact('projectRecord', 'contract', 'receipts', 'canVerify'));
    }

    // A draft receipt may be rejected; verified receipts are never silently erased.
    public function voidReceipt(Request $request, int $project, int $receipt)
    {
        $this->authorizePayment($request, 'receipts.verify', $project);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        DB::transaction(function () use ($request, $project, $receipt, $data) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $row = DB::table('client_payment_receipts')->where('contract_id', $contract->id)
                ->where('id', $receipt)->lockForUpdate()->first();
            abort_unless($row, 404);
            if ($row->status !== 'draft') {
                throw ValidationException::withMessages(['reason' => 'Only pending receipts can be rejected. Verified receipts require a separately approved reversal.']);
            }
            DB::table('client_payment_receipts')->where('id', $receipt)->update([
                'status' => 'void', 'updated_at' => now(),
            ]);
            DB::table('client_payment_events')->insert([
                'contract_id' => $contract->id, 'milestone_id' => $row->milestone_id,
                'receipt_id' => $receipt, 'actor_guard' => 'web',
                'actor_id' => $request->user()->id, 'action' => 'receipt_rejected',
                'before_values' => json_encode(['status' => 'draft', 'amount' => $row->amount]),
                'after_values' => json_encode(['status' => 'void']),
                'reason' => $data['reason'], 'ip_address' => $request->ip(), 'created_at' => now(),
            ]);
        });
        return redirect()->route('client-control.admin.payments.receipts.index', $project)
            ->with('success', 'Pending receipt rejected. The original record and audit history were preserved.');
    }

}
