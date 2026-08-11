<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Dpr;
use App\Models\DprMachineryTool;
use App\Models\DprPhoto;
use App\Models\LabourAttendance;
use App\Models\MaterialConsumed;
use App\Models\MaterialReceived;
use App\Models\MaterialRequirement;
use App\Models\MachineryTool;
use App\Models\Project;
use App\Models\SiteIssue;
use App\Models\TomorrowPlan;
use App\Models\WorkDoneItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DprController extends Controller
{
    private function userRole(): ?string
    {
        return auth()->user()?->role?->name;
    }

    private function isEngineer(): bool
    {
        return $this->userRole() === 'Engineer';
    }

    private function isPmoReviewer(): bool
    {
        return in_array(
            $this->userRole(),
            ['Admin', 'PMO', 'DGM'],
            true
        );
    }

    private function ensureEngineerProjectAccess(
        int $projectId
    ): void {
        if (! $this->isEngineer()) {
            return;
        }

        $hasAccess = auth()->user()
            ->projects()
            ->where(
                'projects.id',
                $projectId
            )
            ->exists();

        abort_unless(
            $hasAccess,
            403,
            'You are not assigned to this Project.'
        );
    }

    private function ensureDprAccess(
        Dpr $dpr
    ): void {
        if (! $this->isEngineer()) {
            return;
        }

        abort_unless(
            (int) $dpr->user_id
                === (int) auth()->id(),
            403,
            'Unauthorized DPR access.'
        );
    }

    private function ensureEditable(
        Dpr $dpr
    ): ?RedirectResponse {
        $this->ensureDprAccess($dpr);

        if ($dpr->status === 'Approved') {
            return redirect()
                ->route('dprs.index')
                ->with(
                    'error',
                    'Approved DPR cannot be edited.'
                );
        }

        if (
            $this->isEngineer()
            && ! in_array(
                $dpr->status,
                ['Pending', 'Rejected'],
                true
            )
        ) {
            return redirect()
                ->route('dprs.index')
                ->with(
                    'error',
                    'This DPR cannot be edited at the current stage.'
                );
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $query = Dpr::query()
            ->with([
                'project',
                'user',
            ]);

        if ($this->isEngineer()) {
            $query->where(
                'user_id',
                auth()->id()
            );
        }

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        if (
            $request->filled('user_id')
            && ! $this->isEngineer()
        ) {
            $query->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        if ($request->filled('from_date')) {
            $query->whereDate(
                'dpr_date',
                '>=',
                $request->input('from_date')
            );
        }

        if ($request->filled('to_date')) {
            $query->whereDate(
                'dpr_date',
                '<=',
                $request->input('to_date')
            );
        }

        $dprs = $query
            ->orderByDesc('dpr_date')
            ->orderByDesc('id')
            ->get();

        if ($this->isEngineer()) {
            $projects = auth()->user()
                ->projects()
                ->orderBy('project_name')
                ->get();

            $engineers = collect([
                auth()->user(),
            ]);
        } else {
            $projects = Project::query()
                ->orderBy('project_name')
                ->get();

            $engineers = \App\Models\User::query()
                ->whereHas(
                    'role',
                    fn (Builder $query) =>
                        $query->where(
                            'name',
                            'Engineer'
                        )
                )
                ->orderBy('name')
                ->get();
        }

        return view(
            'dprs.index',
            compact(
                'dprs',
                'projects',
                'engineers'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | New DPR Review / Orchestration Screen
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'dprs.create',
            [
                'projects' =>
                    $this->availableProjects(),

                'machineries' =>
                    MachineryTool::query()
                        ->orderBy('machine_name')
                        ->get(),
            ]
        );
    }

    /**
     * AJAX endpoint for the new DPR Review screen.
     *
     * It finds standalone execution transactions for:
     * Project + DPR Date + logged-in Engineer.
     *
     * Material Required and Tomorrow Plan are matched by created_at because
     * their operational date normally points to the future requirement /
     * planned execution date.
     */
    public function executionData(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'dpr_date' => [
                'required',
                'date',
            ],
        ]);

        $projectId =
            (int) $validated['project_id'];

        $dprDate =
            $validated['dpr_date'];

        $this->ensureEngineerProjectAccess(
            $projectId
        );

        $existingDpr = Dpr::query()
            ->where(
                'project_id',
                $projectId
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->whereDate(
                'dpr_date',
                $dprDate
            )
            ->first();

        $data = $this->executionCollections(
            projectId: $projectId,
            dprDate: $dprDate,
            engineerId: auth()->id()
        );

        return response()->json([
            'success' => true,

            'existing_dpr' =>
                $existingDpr
                    ? [
                        'id' =>
                            $existingDpr->id,

                        'status' =>
                            $existingDpr->status,

                        'show_url' =>
                            route(
                                'dprs.show',
                                $existingDpr
                            ),
                    ]
                    : null,

            'counts' => [
                'labour_attendances' =>
                    $data['labourAttendances']->count(),

                'work_done' =>
                    $data['workDoneItems']->count(),

                'material_received' =>
                    $data['materialReceived']->count(),

                'material_consumed' =>
                    $data['materialConsumed']->count(),

                'material_required' =>
                    $data['materialRequirements']->count(),

                'site_issues' =>
                    $data['siteIssues']->count(),

                'tomorrow_plans' =>
                    $data['tomorrowPlans']->count(),
            ],

            'labour_attendances' =>
                $data['labourAttendances']
                    ->map(
                        fn (LabourAttendance $attendance): array =>
                            $this->attendancePayload(
                                $attendance
                            )
                    )
                    ->values(),

            'work_done' =>
                $data['workDoneItems']
                    ->map(
                        fn (WorkDoneItem $item): array =>
                            $this->workDonePayload(
                                $item
                            )
                    )
                    ->values(),

            'material_received' =>
                $data['materialReceived']
                    ->map(
                        fn (MaterialReceived $receipt): array =>
                            $this->materialReceivedPayload(
                                $receipt
                            )
                    )
                    ->values(),

            'material_consumed' =>
                $data['materialConsumed']
                    ->map(
                        fn (MaterialConsumed $consumption): array =>
                            $this->materialConsumedPayload(
                                $consumption
                            )
                    )
                    ->values(),

            'material_required' =>
                $data['materialRequirements']
                    ->map(
                        fn (MaterialRequirement $requirement): array =>
                            $this->materialRequirementPayload(
                                $requirement
                            )
                    )
                    ->values(),

            'site_issues' =>
                $data['siteIssues']
                    ->map(
                        fn (SiteIssue $issue): array =>
                            $this->siteIssuePayload(
                                $issue
                            )
                    )
                    ->values(),

            'tomorrow_plans' =>
                $data['tomorrowPlans']
                    ->map(
                        fn (TomorrowPlan $plan): array =>
                            $this->tomorrowPlanPayload(
                                $plan
                            )
                    )
                    ->values(),
        ]);
    }

    /**
     * Create ONLY the DPR header and link selected standalone execution rows.
     * No Work Done, Material, Site Issue or Tomorrow Plan record is duplicated.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'dpr_date' => [
                'required',
                'date',
            ],

            'weather' => [
                'nullable',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'labour_attendance_ids' => [
                'nullable',
                'array',
            ],

            'labour_attendance_ids.*' => [
                'integer',
                'distinct',
                'exists:labour_attendances,id',
            ],

            'work_done_item_ids' => [
                'nullable',
                'array',
            ],

            'work_done_item_ids.*' => [
                'integer',
                'distinct',
                'exists:work_done_items,id',
            ],

            'material_received_ids' => [
                'nullable',
                'array',
            ],

            'material_received_ids.*' => [
                'integer',
                'distinct',
                'exists:material_receiveds,id',
            ],

            'material_consumed_ids' => [
                'nullable',
                'array',
            ],

            'material_consumed_ids.*' => [
                'integer',
                'distinct',
                'exists:material_consumeds,id',
            ],

            'material_requirement_ids' => [
                'nullable',
                'array',
            ],

            'material_requirement_ids.*' => [
                'integer',
                'distinct',
                'exists:material_requirements,id',
            ],

            'site_issue_ids' => [
                'nullable',
                'array',
            ],

            'site_issue_ids.*' => [
                'integer',
                'distinct',
                'exists:site_issues,id',
            ],

            'tomorrow_plan_ids' => [
                'nullable',
                'array',
            ],

            'tomorrow_plan_ids.*' => [
                'integer',
                'distinct',
                'exists:tomorrow_plans,id',
            ],

            'machinery' => [
                'nullable',
                'array',
                'max:50',
            ],

            'machinery.*.machinery_tool_id' => [
                'nullable',
                'integer',
                'exists:machinery_tools,id',
            ],

            'machinery.*.quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:9999',
            ],

            'machinery.*.usage_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],

            'machinery.*.working_condition' => [
                'nullable',
                'string',
                'in:Working,Breakdown,Idle',
            ],

            'machinery.*.remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'photos' => [
                'nullable',
                'array',
                'max:20',
            ],

            'photos.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        $projectId =
            (int) $validated['project_id'];

        $dprDate =
            $validated['dpr_date'];

        $engineerId =
            (int) auth()->id();

        $this->ensureEngineerProjectAccess(
            $projectId
        );

        $existingDpr = Dpr::query()
            ->where(
                'project_id',
                $projectId
            )
            ->where(
                'user_id',
                $engineerId
            )
            ->whereDate(
                'dpr_date',
                $dprDate
            )
            ->first();

        if ($existingDpr) {
            throw ValidationException::withMessages([
                'dpr_date' => [
                    "A DPR already exists for this Project and Date with status '{$existingDpr->status}'. Open that DPR instead of creating a duplicate.",
                ],
            ]);
        }

        $eligible = $this->executionCollections(
            projectId: $projectId,
            dprDate: $dprDate,
            engineerId: $engineerId
        );

        $selected = [
            'labourAttendances' =>
                $this->selectedIds(
                    $validated['labour_attendance_ids']
                    ?? []
                ),

            'workDoneItems' =>
                $this->selectedIds(
                    $validated['work_done_item_ids']
                    ?? []
                ),

            'materialReceived' =>
                $this->selectedIds(
                    $validated['material_received_ids']
                    ?? []
                ),

            'materialConsumed' =>
                $this->selectedIds(
                    $validated['material_consumed_ids']
                    ?? []
                ),

            'materialRequirements' =>
                $this->selectedIds(
                    $validated['material_requirement_ids']
                    ?? []
                ),

            'siteIssues' =>
                $this->selectedIds(
                    $validated['site_issue_ids']
                    ?? []
                ),

            'tomorrowPlans' =>
                $this->selectedIds(
                    $validated['tomorrow_plan_ids']
                    ?? []
                ),
        ];

        $this->assertSelectionsEligible(
            selected: $selected,
            eligible: $eligible
        );

        $machineryRows = collect(
            $validated['machinery'] ?? []
        )
            ->filter(
                fn (array $row): bool =>
                    ! empty($row['machinery_tool_id'])
            )
            ->values();

        if (
            collect($selected)
                ->every(
                    fn (Collection $ids): bool =>
                        $ids->isEmpty()
                )
            && $machineryRows->isEmpty()
            && ! $request->hasFile('photos')
        ) {
            throw ValidationException::withMessages([
                'project_id' => [
                    'Select at least one execution record, add Machinery / Equipment, or upload a DPR photo before submitting.',
                ],
            ]);
        }

        $dpr = DB::transaction(
            function () use (
                $validated,
                $projectId,
                $dprDate,
                $engineerId,
                $selected,
                $machineryRows,
                $request
            ): Dpr {
                $dpr = Dpr::create([
                    'project_id' =>
                        $projectId,

                    'user_id' =>
                        $engineerId,

                    'dpr_date' =>
                        $dprDate,

                    'weather' =>
                        $this->nullableTrim(
                            $validated['weather']
                            ?? null
                        ),

                    'remarks' =>
                        $this->nullableTrim(
                            $validated['remarks']
                            ?? null
                        ),

                    'status' =>
                        'Pending',

                    'pmo_remarks' =>
                        null,
                ]);

                if (
                    $selected['labourAttendances']
                        ->isNotEmpty()
                ) {
                    $pivot = $selected[
                        'labourAttendances'
                    ]
                        ->mapWithKeys(
                            fn (int $id): array => [
                                $id => [
                                    'created_by' =>
                                        $engineerId,
                                ],
                            ]
                        )
                        ->all();

                    $dpr->labourAttendances()
                        ->attach($pivot);
                }

                WorkDoneItem::query()
                    ->whereIn(
                        'id',
                        $selected['workDoneItems']
                    )
                    ->update([
                        'dpr_id' =>
                            $dpr->id,
                    ]);

                MaterialReceived::query()
                    ->whereIn(
                        'id',
                        $selected['materialReceived']
                    )
                    ->update([
                        'dpr_id' =>
                            $dpr->id,
                    ]);

                MaterialConsumed::query()
                    ->whereIn(
                        'id',
                        $selected['materialConsumed']
                    )
                    ->update([
                        'dpr_id' =>
                            $dpr->id,
                    ]);

                MaterialRequirement::query()
                    ->whereIn(
                        'id',
                        $selected['materialRequirements']
                    )
                    ->update([
                        'dpr_id' =>
                            $dpr->id,
                    ]);

                SiteIssue::query()
                    ->whereIn(
                        'id',
                        $selected['siteIssues']
                    )
                    ->update([
                        'dpr_id' =>
                            $dpr->id,
                    ]);

                TomorrowPlan::query()
                    ->whereIn(
                        'id',
                        $selected['tomorrowPlans']
                    )
                    ->update([
                        'dpr_id' =>
                            $dpr->id,
                    ]);

                foreach ($machineryRows as $row) {
                    DprMachineryTool::create([
                        'dpr_id' =>
                            $dpr->id,

                        'machinery_tool_id' =>
                            (int) $row['machinery_tool_id'],

                        'quantity' =>
                            (int) ($row['quantity'] ?? 1),

                        'usage_hours' =>
                            (float) ($row['usage_hours'] ?? 0),

                        'working_condition' =>
                            $row['working_condition']
                            ?? 'Working',

                        'remarks' =>
                            $this->nullableTrim(
                                $row['remarks'] ?? null
                            ),
                    ]);
                }

                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $path = $photo->store(
                            'dpr_photos',
                            'public'
                        );

                        DprPhoto::create([
                            'dpr_id' =>
                                $dpr->id,

                            'photo_path' =>
                                $path,
                        ]);
                    }
                }

                AuditHelper::log(
                    'DPR',
                    'Created',
                    'Dpr',
                    $dpr->id,
                    'DPR created from standalone daily execution records and submitted for PMO review.',
                    null,
                    [
                        'id' =>
                            $dpr->id,

                        'project_id' =>
                            $projectId,

                        'user_id' =>
                            $engineerId,

                        'dpr_date' =>
                            $dprDate,

                        'status' =>
                            'Pending',

                        'linked_labour_attendance_ids' =>
                            $selected[
                                'labourAttendances'
                            ]->all(),

                        'linked_work_done_item_ids' =>
                            $selected[
                                'workDoneItems'
                            ]->all(),

                        'linked_material_received_ids' =>
                            $selected[
                                'materialReceived'
                            ]->all(),

                        'linked_material_consumed_ids' =>
                            $selected[
                                'materialConsumed'
                            ]->all(),

                        'linked_material_requirement_ids' =>
                            $selected[
                                'materialRequirements'
                            ]->all(),

                        'linked_site_issue_ids' =>
                            $selected[
                                'siteIssues'
                            ]->all(),

                        'linked_tomorrow_plan_ids' =>
                            $selected[
                                'tomorrowPlans'
                            ]->all(),

                        'machinery_count' =>
                            $machineryRows->count(),

                        'dpr_photo_count' =>
                            $request->hasFile('photos')
                                ? count($request->file('photos'))
                                : 0,
                    ]
                );

                return $dpr;
            }
        );

        return redirect()
            ->route(
                'dprs.show',
                $dpr
            )
            ->with(
                'success',
                'DPR submitted successfully for PMO review.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PMO Review
    |--------------------------------------------------------------------------
    */

    public function pmoQueue(): View
    {
        abort_unless(
            $this->isPmoReviewer(),
            403
        );

        $dprs = Dpr::query()
            ->with([
                'project',
                'user',
            ])
            ->where(
                'status',
                'Pending'
            )
            ->orderByDesc('dpr_date')
            ->orderByDesc('id')
            ->get();

        return view(
            'dprs.pmo-queue',
            compact('dprs')
        );
    }

    public function approve(
        Request $request,
        int $id
    ): RedirectResponse {
        abort_unless(
            $this->isPmoReviewer(),
            403
        );

        $validated = $request->validate([
            'pmo_remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $dpr = Dpr::findOrFail($id);

        if ($dpr->status !== 'Pending') {
            return redirect()
                ->route('dprs.pmo-queue')
                ->with(
                    'error',
                    'Only Pending DPRs can be approved.'
                );
        }

        $oldValues =
            $dpr->only([
                'status',
                'pmo_remarks',
            ]);

        $dpr->update([
            'status' =>
                'Approved',

            'pmo_remarks' =>
                $this->nullableTrim(
                    $validated['pmo_remarks']
                    ?? null
                ),
        ]);

        AuditHelper::log(
            'DPR',
            'Approved',
            'Dpr',
            $dpr->id,
            'DPR approved by PMO/Admin.',
            $oldValues,
            $dpr->only([
                'status',
                'pmo_remarks',
            ])
        );

        return redirect()
            ->route('dprs.pmo-queue')
            ->with(
                'success',
                'DPR approved successfully.'
            );
    }

    public function reject(
        Request $request,
        int $id
    ): RedirectResponse {
        abort_unless(
            $this->isPmoReviewer(),
            403
        );

        $validated = $request->validate([
            'pmo_remarks' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $dpr = Dpr::findOrFail($id);

        if ($dpr->status !== 'Pending') {
            return redirect()
                ->route('dprs.pmo-queue')
                ->with(
                    'error',
                    'Only Pending DPRs can be returned for correction.'
                );
        }

        $oldValues =
            $dpr->only([
                'status',
                'pmo_remarks',
            ]);

        $dpr->update([
            'status' =>
                'Rejected',

            'pmo_remarks' =>
                trim(
                    $validated['pmo_remarks']
                ),
        ]);

        AuditHelper::log(
            'DPR',
            'Rejected',
            'Dpr',
            $dpr->id,
            'DPR returned to Engineer for correction.',
            $oldValues,
            $dpr->only([
                'status',
                'pmo_remarks',
            ])
        );

        return redirect()
            ->route('dprs.pmo-queue')
            ->with(
                'success',
                'DPR returned to Engineer for correction.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show / PDF
    |--------------------------------------------------------------------------
    |
    | The next DPR phase replaces the Blade files. Both new standalone and
    | legacy relationships are loaded now so historical records remain safe.
    |
    */

    public function show(
        int $id
    ): View {
        $dpr = $this->loadDprForDisplay(
            $id
        );

        $this->ensureDprAccess(
            $dpr
        );

        return view(
            'dprs.show',
            compact('dpr')
        );
    }

    public function downloadPdf(
        int $id
    ) {
        $dpr = $this->loadDprForDisplay(
            $id
        );

        $this->ensureDprAccess(
            $dpr
        );

        $pdf = Pdf::loadView(
            'dprs.pdf',
            compact('dpr')
        );

        $date = $dpr->dpr_date
            ?->format('d-m-Y')
            ?? 'DPR';

        $fileName = implode('_', [
            $date,

            str_replace(
                ' ',
                '-',
                $dpr->project?->project_name
                ?? 'Project'
            ),

            str_replace(
                ' ',
                '-',
                $dpr->user?->name
                ?? 'Engineer'
            ),
        ]) . '.pdf';

        return $pdf->download(
            $fileName
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit / Update
    |--------------------------------------------------------------------------
    |
    | New orchestration-based Edit is the next phase. To protect linked
    | standalone records, new integrated DPRs are not sent through the old
    | destructive update flow.
    |
    */

    public function edit(
        int $id
    ) {
        $dpr = Dpr::findOrFail($id);

        if (
            $response =
                $this->ensureEditable($dpr)
        ) {
            return $response;
        }

        if (
            $dpr->hasStandaloneExecutionData()
        ) {
            return redirect()
                ->route(
                    'dprs.show',
                    $dpr
                )
                ->with(
                    'error',
                    'This DPR uses the new integrated execution workflow. The integrated correction screen will be enabled in the next DPR phase.'
                );
        }

        /*
         * Preserve existing historical Edit page for legacy DPRs only.
         * The old view can continue to use its legacy relationships.
         */
        return redirect()
            ->route(
                'dprs.show',
                $dpr
            )
            ->with(
                'error',
                'Legacy DPR editing is temporarily disabled while the integrated DPR correction workflow is being completed.'
            );
    }

    public function update(
        Request $request,
        int $id
    ): RedirectResponse {
        $dpr = Dpr::findOrFail($id);

        if (
            $response =
                $this->ensureEditable($dpr)
        ) {
            return $response;
        }

        return redirect()
            ->route(
                'dprs.show',
                $dpr
            )
            ->with(
                'error',
                'DPR correction will be available through the new integrated correction screen.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        int $id
    ): RedirectResponse {
        $dpr = $this->loadDprForDisplay(
            $id
        );

        $this->ensureDprAccess(
            $dpr
        );

        if ($dpr->status === 'Approved') {
            return redirect()
                ->route('dprs.index')
                ->with(
                    'error',
                    'Approved DPR cannot be deleted.'
                );
        }

        DB::transaction(
            function () use ($dpr): void {
                $oldValues = [
                    'id' =>
                        $dpr->id,

                    'project_id' =>
                        $dpr->project_id,

                    'user_id' =>
                        $dpr->user_id,

                    'dpr_date' =>
                        $dpr->dpr_date
                            ?->format('Y-m-d'),

                    'status' =>
                        $dpr->status,

                    'linked_execution_count' =>
                        $dpr->standalone_execution_count,
                ];

                $dpr->labourAttendances()
                    ->detach();

                WorkDoneItem::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->update([
                        'dpr_id' =>
                            null,
                    ]);

                MaterialReceived::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->update([
                        'dpr_id' =>
                            null,
                    ]);

                MaterialConsumed::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->update([
                        'dpr_id' =>
                            null,
                    ]);

                MaterialRequirement::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->update([
                        'dpr_id' =>
                            null,
                    ]);

                SiteIssue::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->update([
                        'dpr_id' =>
                            null,
                    ]);

                TomorrowPlan::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->update([
                        'dpr_id' =>
                            null,
                    ]);

                $photoPaths = DprPhoto::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->pluck('photo_path')
                    ->filter()
                    ->values()
                    ->all();

                DprMachineryTool::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->delete();

                DprPhoto::query()
                    ->where(
                        'dpr_id',
                        $dpr->id
                    )
                    ->delete();

                if ($photoPaths !== []) {
                    Storage::disk('public')
                        ->delete($photoPaths);
                }

                $dprId =
                    $dpr->id;

                $dpr->delete();

                AuditHelper::log(
                    'DPR',
                    'Deleted',
                    'Dpr',
                    $dprId,
                    'DPR deleted and standalone execution records released.',
                    $oldValues,
                    null
                );
            }
        );

        return redirect()
            ->route('dprs.index')
            ->with(
                'success',
                'DPR deleted successfully. Linked standalone execution records are available again.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Labour Attendance Endpoint
    |--------------------------------------------------------------------------
    |
    | Kept temporarily because other pages/routes may still call it.
    |
    */

    public function labourAttendance(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'attendance_date' => [
                'required',
                'date',
            ],
        ]);

        $projectId =
            (int) $validated['project_id'];

        $attendanceDate =
            $validated['attendance_date'];

        $this->ensureEngineerProjectAccess(
            $projectId
        );

        $attendances =
            $this->eligibleLabourAttendances(
                projectId: $projectId,
                dprDate: $attendanceDate,
                engineerId: auth()->id()
            );

        return response()->json([
            'success' =>
                true,

            'attendance_found' =>
                $attendances->isNotEmpty(),

            'attendance_count' =>
                $attendances->count(),

            'attendance_ids' =>
                $attendances
                    ->pluck('id')
                    ->values(),

            'attendances' =>
                $attendances
                    ->map(
                        fn (
                            LabourAttendance $attendance
                        ): array =>
                            $this->attendancePayload(
                                $attendance
                            )
                    )
                    ->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Execution Queries
    |--------------------------------------------------------------------------
    */

    private function executionCollections(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): array {
        return [
            'labourAttendances' =>
                $this->eligibleLabourAttendances(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),

            'workDoneItems' =>
                $this->eligibleWorkDoneItems(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),

            'materialReceived' =>
                $this->eligibleMaterialReceived(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),

            'materialConsumed' =>
                $this->eligibleMaterialConsumed(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),

            'materialRequirements' =>
                $this->eligibleMaterialRequirements(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),

            'siteIssues' =>
                $this->eligibleSiteIssues(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),

            'tomorrowPlans' =>
                $this->eligibleTomorrowPlans(
                    $projectId,
                    $dprDate,
                    $engineerId
                ),
        ];
    }

    private function eligibleLabourAttendances(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return LabourAttendance::query()
            ->with([
                'shift',
                'details.labour',
                'details.attendanceStatus',
                'details.designationRole',
            ])
            ->where(
                'project_id',
                $projectId
            )
            ->whereDate(
                'attendance_date',
                $dprDate
            )
            ->where(
                'is_active',
                true
            )
            ->whereIn(
                'status',
                [
                    'submitted',
                    'approved',
                    'reopened',
                ]
            )
            ->whereDoesntHave(
                'dprs'
            )
            ->orderBy('shift_id')
            ->orderBy('id')
            ->get();
    }

    private function eligibleWorkDoneItems(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return WorkDoneItem::query()
            ->with([
                'header.project',
                'header.engineer',
                'activity',
                'activityMapping.division',
                'contractor',
                'block',
                'floor',
                'unitLocation',
                'room',
                'subspace',
                'photos',
            ])
            ->whereNull('dpr_id')
            ->whereHas(
                'header',
                function (
                    Builder $query
                ) use (
                    $projectId,
                    $dprDate,
                    $engineerId
                ): void {
                    $query
                        ->where(
                            'project_id',
                            $projectId
                        )
                        ->whereDate(
                            'work_date',
                            $dprDate
                        )
                        ->where(
                            'user_id',
                            $engineerId
                        );
                }
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function eligibleMaterialReceived(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return MaterialReceived::query()
            ->with([
                'project',
                'engineer',
                'vendor',
                'items.materialType',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',
                'photos',
            ])
            ->whereNull('dpr_id')
            ->where(
                'project_id',
                $projectId
            )
            ->whereDate(
                'received_date',
                $dprDate
            )
            ->where(
                'user_id',
                $engineerId
            )
            ->orderBy('received_time')
            ->orderBy('id')
            ->get();
    }

    private function eligibleMaterialConsumed(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return MaterialConsumed::query()
            ->with([
                'project',
                'engineer',
                'items.materialType',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',
            ])
            ->whereNull('dpr_id')
            ->where(
                'project_id',
                $projectId
            )
            ->whereDate(
                'consumed_date',
                $dprDate
            )
            ->where(
                'user_id',
                $engineerId
            )
            ->orderBy('consumed_time')
            ->orderBy('id')
            ->get();
    }

    private function eligibleMaterialRequirements(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return MaterialRequirement::query()
            ->with([
                'project',
                'block',
                'creator',
                'items.materialType',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',
                'material',
            ])
            ->whereNull('dpr_id')
            ->where(
                'project_id',
                $projectId
            )
            ->whereDate(
                'created_at',
                $dprDate
            )
            ->orderBy('id')
            ->get();
    }

    private function eligibleSiteIssues(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return SiteIssue::query()
            ->with([
                'project',
                'activity',
                'creator',
                'block',
                'floor',
                'unit',
                'room',
                'subspace',
                'photos',
            ])
            ->whereNull('dpr_id')
            ->where(
                'project_id',
                $projectId
            )
            ->whereDate(
                'issue_date',
                $dprDate
            )
            ->where(
                'created_by',
                $engineerId
            )
            ->orderBy('id')
            ->get();
    }

    private function eligibleTomorrowPlans(
        int $projectId,
        string $dprDate,
        int $engineerId
    ): Collection {
        return TomorrowPlan::query()
            ->with([
                'project',
                'activity',
                'contractor',
                'block',
                'floor',
                'unit',
                'room',
                'subspace',
            ])
            ->whereNull('dpr_id')
            ->where(
                'project_id',
                $projectId
            )
            ->where(
                'created_by',
                $engineerId
            )
            ->whereDate(
                'created_at',
                $dprDate
            )
            ->orderBy('planned_date')
            ->orderBy('id')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Selection Safety
    |--------------------------------------------------------------------------
    */

    private function selectedIds(
        array $ids
    ): Collection {
        return collect($ids)
            ->filter(
                fn ($id): bool =>
                    $id !== null
                    && $id !== ''
            )
            ->map(
                fn ($id): int =>
                    (int) $id
            )
            ->unique()
            ->values();
    }

    private function assertSelectionsEligible(
        array $selected,
        array $eligible
    ): void {
        $labels = [
            'labourAttendances' =>
                'Labour Attendance',

            'workDoneItems' =>
                'Work Done',

            'materialReceived' =>
                'Material Received',

            'materialConsumed' =>
                'Material Consumed',

            'materialRequirements' =>
                'Material Required',

            'siteIssues' =>
                'Site Issues',

            'tomorrowPlans' =>
                'Tomorrow Plan',
        ];

        $errors = [];

        foreach (
            $selected as $key => $ids
        ) {
            /** @var Collection $ids */
            $eligibleIds =
                $eligible[$key]
                    ->pluck('id')
                    ->map(
                        fn ($id): int =>
                            (int) $id
                    );

            $invalid =
                $ids->diff(
                    $eligibleIds
                );

            if ($invalid->isNotEmpty()) {
                $errors[$key][] =
                    "{$labels[$key]} selection contains a record that is already linked, belongs to another Project/Date/Engineer, or is otherwise unavailable.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | JSON Payload Helpers
    |--------------------------------------------------------------------------
    */

    private function attendancePayload(
        LabourAttendance $attendance
    ): array {
        $details =
            $attendance->details;

        return [
            'id' =>
                $attendance->id,

            'attendance_number' =>
                $attendance->attendance_number
                ?: "Attendance #{$attendance->id}",

            'shift' =>
                $attendance->shift?->shift_name
                ?? $attendance->shift?->name
                ?? 'General Shift',

            'status' =>
                $attendance->status,

            'total_labour' =>
                $details
                    ->pluck('labour_id')
                    ->filter()
                    ->unique()
                    ->count(),

            'normal_hours' =>
                round(
                    (float) $details
                        ->sum('normal_hours'),
                    2
                ),

            'ot_hours' =>
                round(
                    (float) $details
                        ->sum('ot_hours'),
                    2
                ),

            'view_url' =>
                route(
                    'labour-attendances.show',
                    $attendance
                ),
        ];
    }

    private function workDonePayload(
        WorkDoneItem $item
    ): array {
        return [
            'id' =>
                $item->id,

            'activity' =>
                $item->activity_name
                ?? $item->activity?->activity_name
                ?? 'Work Activity',

            'division' =>
                $item->activityMapping
                    ?->division
                    ?->name,

            'quantity' =>
                $this->quantity(
                    $item->quantity_completed
                ),

            'unit' =>
                $item->unit
                ?? $item->activityMapping?->unit
                ?? $item->activity?->unit,

            'location' =>
                $item->location_path
                ?: '-',

            'contractor' =>
                $item->contractor?->contractor_name,

            'status' =>
                $item->execution_status
                ?? 'Recorded',

            'photo_count' =>
                $item->photos->count(),
        ];
    }

    private function materialReceivedPayload(
        MaterialReceived $receipt
    ): array {
        return [
            'id' =>
                $receipt->id,

            'reference' =>
                "Receipt #{$receipt->id}",

            'vendor' =>
                $receipt->vendor?->vendor_name
                ?? $receipt->vendor_name,

            'status' =>
                $receipt->status
                ?? 'Recorded',

            'challan' =>
                $receipt->challan_number,

            'items' =>
                $this->materialItemLines(
                    $receipt,
                    'quantity_received'
                ),

            'photo_count' =>
                $receipt->photos->count(),
        ];
    }

    private function materialConsumedPayload(
        MaterialConsumed $consumption
    ): array {
        return [
            'id' =>
                $consumption->id,

            'reference' =>
                "Consumption #{$consumption->id}",

            'status' =>
                $consumption->status
                ?? 'Recorded',

            'items' =>
                $this->materialItemLines(
                    $consumption,
                    'quantity_consumed'
                ),

            'wastage' =>
                $this->quantity(
                    $consumption
                        ->total_wastage_quantity
                        ?? $consumption
                            ->wastage_quantity
                        ?? 0
                ),
        ];
    }

    private function materialRequirementPayload(
        MaterialRequirement $requirement
    ): array {
        return [
            'id' =>
                $requirement->id,

            'reference' =>
                "Requirement #{$requirement->id}",

            'required_date' =>
                $requirement->required_date
                    ?->format('d/m/Y'),

            'priority' =>
                $requirement->priority,

            'status' =>
                $requirement->status,

            'items' =>
                $this->materialRequirementLines(
                    $requirement
                ),
        ];
    }

    private function siteIssuePayload(
        SiteIssue $issue
    ): array {
        return [
            'id' =>
                $issue->id,

            'title' =>
                $issue->title,

            'type' =>
                $issue->issue_type,

            'priority' =>
                $issue->priority,

            'status' =>
                $issue->status,

            'responsible' =>
                $issue->responsible_person,

            'location' =>
                $issue->location_path
                ?: '-',

            'photo_count' =>
                $issue->photos->count(),
        ];
    }

    private function tomorrowPlanPayload(
        TomorrowPlan $plan
    ): array {
        $location = collect([
            $plan->block?->name,
            $plan->floor?->name,
            $plan->unit?->name,
            $plan->room?->name,
            $plan->subspace?->name,
        ])
            ->filter()
            ->implode(' → ');

        return [
            'id' =>
                $plan->id,

            'activity' =>
                $plan->activity?->activity_name
                ?? 'Planned Activity',

            'planned_date' =>
                $plan->planned_date
                    ? Carbon::parse(
                        $plan->planned_date
                    )->format('d/m/Y')
                    : null,

            'quantity' =>
                $this->quantity(
                    $plan->planned_quantity
                ),

            'unit' =>
                $plan->unit,

            'planned_labour' =>
                $plan->planned_labour,

            'priority' =>
                $plan->priority,

            'status' =>
                $plan->status,

            'location' =>
                $location ?: '-',
        ];
    }

    private function materialItemLines(
        object $header,
        string $quantityField
    ): array {
        if (
            method_exists(
                $header,
                'items'
            )
            && $header->relationLoaded(
                'items'
            )
            && $header->items->isNotEmpty()
        ) {
            return $header->items
                ->map(
                    function (
                        $item
                    ) use (
                        $quantityField
                    ): array {
                        return [
                            'name' =>
                                $item->display_name
                                ?: $item->materialType
                                    ?->material_type_name
                                ?: 'Material',

                            'quantity' =>
                                $this->quantity(
                                    $item->{$quantityField}
                                    ?? 0
                                ),

                            'unit' =>
                                $item->unit?->unit_name
                                ?? $item->unit?->name
                                ?? '',
                        ];
                    }
                )
                ->values()
                ->all();
        }

        return [[
            'name' =>
                $header->material_name
                ?? $header->material
                    ?->material_name
                ?? 'Material',

            'quantity' =>
                $this->quantity(
                    $header->{$quantityField}
                    ?? 0
                ),

            'unit' =>
                $header->unit
                ?? '',
        ]];
    }

    private function materialRequirementLines(
        MaterialRequirement $requirement
    ): array {
        if (
            $requirement->relationLoaded(
                'items'
            )
            && $requirement->items
                ->isNotEmpty()
        ) {
            return $requirement->items
                ->map(
                    fn ($item): array => [
                        'name' =>
                            $item->display_name
                            ?: $item->materialType
                                ?->material_type_name
                            ?: 'Material',

                        'quantity' =>
                            $this->quantity(
                                $item->required_quantity
                            ),

                        'unit' =>
                            $item->unit?->unit_name
                            ?? $item->unit?->name
                            ?? '',
                    ]
                )
                ->values()
                ->all();
        }

        return [[
            'name' =>
                $requirement->material
                    ?->material_name
                ?? 'Material',

            'quantity' =>
                $this->quantity(
                    $requirement
                        ->required_quantity
                ),

            'unit' =>
                $requirement->unit
                ?? '',
        ]];
    }

    /*
    |--------------------------------------------------------------------------
    | Display Loading
    |--------------------------------------------------------------------------
    */

    private function loadDprForDisplay(
        int $id
    ): Dpr {
        return Dpr::query()
            ->with([
                'project',
                'user',

                'labourAttendances.shift',
                'labourAttendances.details.labour',
                'labourAttendances.details.attendanceStatus',
                'labourAttendances.details.designationRole',

                'workDoneItems.header',
                'workDoneItems.activity',
                'workDoneItems.activityMapping.division',
                'workDoneItems.contractor',
                'workDoneItems.block',
                'workDoneItems.floor',
                'workDoneItems.unitLocation',
                'workDoneItems.room',
                'workDoneItems.subspace',
                'workDoneItems.photos',

                'materialReceipts.vendor',
                'materialReceipts.items.materialType',
                'materialReceipts.items.brand',
                'materialReceipts.items.specification',
                'materialReceipts.items.grade',
                'materialReceipts.items.unit',
                'materialReceipts.photos',

                'materialConsumptions.items.materialType',
                'materialConsumptions.items.brand',
                'materialConsumptions.items.specification',
                'materialConsumptions.items.grade',
                'materialConsumptions.items.unit',

                'materialRequirements.items.materialType',
                'materialRequirements.items.brand',
                'materialRequirements.items.specification',
                'materialRequirements.items.grade',
                'materialRequirements.items.unit',
                'materialRequirements.material',

                'siteIssues.activity',
                'siteIssues.block',
                'siteIssues.floor',
                'siteIssues.unit',
                'siteIssues.room',
                'siteIssues.subspace',
                'siteIssues.photos',

                'tomorrowPlans.activity',
                'tomorrowPlans.block',
                'tomorrowPlans.floor',
                'tomorrowPlans.unit',
                'tomorrowPlans.room',
                'tomorrowPlans.subspace',

                /*
                 * Legacy DPR relationships:
                 */
                'workItems.activity',
                'workItems.contractor',
                'workItems.block',
                'workItems.floor',
                'workItems.unit',
                'workItems.room',
                'workItems.subspace',
                'workItems.activityMapping.division',
                'photos',
                'labours.labourType',
                'materials.material',
                'materialReceived.material',
                'materialReceived.vendor',
                'materialRequired.material',
                'machineryTools.machineryTool',
            ])
            ->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | General Helpers
    |--------------------------------------------------------------------------
    */

    private function availableProjects(): Collection
    {
        if ($this->isEngineer()) {
            return auth()->user()
                ->projects()
                ->whereNotIn(
                    'projects.status',
                    [
                        'Completed',
                        'Handed Over',
                        'Closed',
                    ]
                )
                ->orderBy(
                    'projects.project_name'
                )
                ->get();
        }

        return Project::query()
            ->whereNotIn(
                'status',
                [
                    'Completed',
                    'Handed Over',
                    'Closed',
                ]
            )
            ->orderBy('project_name')
            ->get();
    }

    private function quantity(
        mixed $value
    ): string {
        if (
            $value === null
            || $value === ''
        ) {
            return '0';
        }

        return rtrim(
            rtrim(
                number_format(
                    (float) $value,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value =
            trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Location AJAX Endpoints
    |--------------------------------------------------------------------------
    */

    public function getFloors(
        int $block
    ): JsonResponse {
        return response()->json(
            \App\Models\ProjectFloor::query()
                ->where(
                    'project_block_id',
                    $block
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('sequence')
                ->get([
                    'id',
                    'name',
                ])
        );
    }

    public function getUnits(
        int $floor
    ): JsonResponse {
        return response()->json(
            \App\Models\ProjectUnit::query()
                ->where(
                    'project_floor_id',
                    $floor
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
        );
    }

    public function getRooms(
        int $unit
    ): JsonResponse {
        return response()->json(
            \App\Models\ProjectRoom::query()
                ->where(
                    'project_unit_id',
                    $unit
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
        );
    }

    public function getSubspaces(
        int $room
    ): JsonResponse {
        return response()->json(
            \App\Models\ProjectSubspace::query()
                ->where(
                    'project_room_id',
                    $room
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
        );
    }
}
