<?php
namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientPaymentPublicationController extends Controller
{
    public function publish(Request $request, int $project)
    {
        $user = $request->user();
        abort_unless($user && $user->isActive() && $user->hasProjectAccess($project)
            && $user->hasPermission('client_control.payments.publish'), 403);

        DB::transaction(function () use ($request, $project, $user) {
            $contract = DB::table('client_payment_contracts')->where('project_id', $project)->lockForUpdate()->first();
            abort_unless($contract, 404);
            $milestones = DB::table('client_payment_milestones')->where('contract_id', $contract->id)
                ->whereNull('archived_at')->orderBy('sort_order')->orderBy('id')->get();
            if ($milestones->isEmpty()) {
                throw ValidationException::withMessages(['schedule' => 'Add payment stages before publishing.']);
            }
            // Publish any configured schedule; no mandatory full allocation or variation matching.
            $verified = DB::table('client_payment_receipts')->where('contract_id', $contract->id)
                ->where('status', 'verified')->orderBy('received_on')->orderBy('id')->get();
            $paymentsByStage = $verified->groupBy('milestone_id')->map(fn ($rows) => round((float) $rows->sum('amount'), 2));
            $stageRows = $milestones->map(function ($m) use ($paymentsByStage) {
                $paid = (float) ($paymentsByStage->get($m->id) ?? 0);
                return [
                    'id' => $m->id, 'name' => $m->title, 'type' => $m->type,
                    'percentage' => $m->type === 'additional' ? null : (float) $m->scheduled_percent,
                    'amount' => (float) $m->scheduled_amount, 'received' => $paid,
                    'balance' => round((float) $m->scheduled_amount - $paid, 2),
                    'expected_date' => $m->planned_due_date,
                ];
            })->values()->all();
            $receiptRows = $verified->map(fn ($r) => [
                'date' => $r->received_on, 'milestone_id' => $r->milestone_id,'amount' => (float) $r->amount,])->values()->all();
            $received = round((float) $verified->sum('amount'), 2);
            $snapshot = [
                'agreement_amount' => (float) $contract->agreed_amount,
                'approved_variations' => (float) $contract->approved_variations,
                'total_agreed' => round((float) $contract->agreed_amount + (float) $contract->approved_variations, 2),
                'total_received' => $received,
                'total_outstanding' => round((float) $contract->agreed_amount + (float) $contract->approved_variations - $received, 2),
                'stages' => $stageRows, 'receipts' => $receiptRows,
                'published_date' => now()->toDateString(),
            ];
            $previous = DB::table('client_payment_publications')->where('contract_id', $contract->id)
                ->orderByDesc('revision')->first();
            $revision = ($previous ? (int) $previous->revision : 0) + 1;
            DB::table('client_payment_publications')->insert([
                'contract_id' => $contract->id, 'revision' => $revision,
                'client_safe_snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'status' => 'published', 'approved_by' => $user->id, 'approved_at' => now(),
                'published_by' => $user->id, 'published_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('client_payment_events')->insert([
                'contract_id' => $contract->id, 'actor_guard' => 'web', 'actor_id' => $user->id,
                'action' => 'client_payment_statement_published',
                'after_values' => json_encode(['publication_revision' => $revision]),
                'ip_address' => $request->ip(), 'created_at' => now(),
            ]);
        });
        return back()->with('success', 'Payment statement published to the client portal.');
    }

    public function clientStatement(int $project, ClientProjectAccess $access)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $access->allowed($client, $project), 403);
        $projectRecord = DB::table('projects')->where('id', $project)->first();
        abort_unless($projectRecord, 404);
        $contract = DB::table('client_payment_contracts')->where('project_id', $project)->first();
        $publication = $contract ? DB::table('client_payment_publications')
            ->where('contract_id', $contract->id)->where('status', 'published')
            ->whereNotNull('published_at')->orderByDesc('revision')->first() : null;
        $statement = $publication ? json_decode($publication->client_safe_snapshot, true) : null;
        return view('client-control.payment-statement', compact('projectRecord', 'statement', 'publication'));
    }
}
