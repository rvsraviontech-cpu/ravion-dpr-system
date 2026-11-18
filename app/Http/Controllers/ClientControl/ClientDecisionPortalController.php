<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClientDecisionPortalController extends Controller
{
    private function authorizeProject(Project $project, ClientProjectAccess $access): int
    {
        $client = Auth::guard('client')->user();
        abort_unless($client, 401);
        $access->assertAllowed($client, (int) $project->id);
        return (int) $client->getKey();
    }

    private function visible(int $projectId)
    {
        return DB::table('client_decisions')
            ->where('project_id', $projectId)
            ->whereNotNull('published_at')
            ->whereNull('withdrawn_at')
            ->whereIn('status', ['Published', 'Responded', 'Acknowledged', 'Closed']);
    }

    public function index(Project $project, ClientProjectAccess $access)
    {
        $clientId = $this->authorizeProject($project, $access);
        $decisions = $this->visible((int) $project->id)
            ->orderByRaw("CASE WHEN status = 'Published' THEN 0 ELSE 1 END")
            ->orderByDesc('published_at')
            ->paginate(15);
        $respondedIds = DB::table('client_decision_responses')
            ->where('client_user_id', $clientId)
            ->whereIn('decision_id', $decisions->pluck('id')->all())
            ->whereIn('response', ['Approve', 'Reject'])
            ->pluck('decision_id')->all();
        return view('client-control.decisions.index', compact('project', 'decisions', 'respondedIds'));
    }

    public function show(Project $project, int $decision, ClientProjectAccess $access)
    {
        $clientId = $this->authorizeProject($project, $access);
        $item = $this->visible((int) $project->id)->where('id', $decision)->first();
        abort_unless($item, 404);
        $options = DB::table('client_decision_options')->where('decision_id', $item->id)->orderBy('sort_order')->get();
        $files = DB::table('client_decision_files')->where('decision_id', $item->id)
            ->select('id', 'original_name', 'mime_type', 'size_bytes')->orderBy('id')->get();
        $responses = DB::table('client_decision_responses')->where('decision_id', $item->id)
            ->where('client_user_id', $clientId)->whereIn('response', ['Approve', 'Reject'])->orderByDesc('id')->get();
        $clarifications = DB::table('client_decision_clarifications')->where('decision_id', $item->id)
            ->orderBy('id')->get();
        $pendingClarification = $clarifications->contains(fn ($c) => $c->replied_at === null);
        return view('client-control.decisions.show', compact('project', 'item', 'options', 'files', 'responses', 'clarifications', 'pendingClarification'));
    }

    public function respond(Request $request, Project $project, int $decision, ClientProjectAccess $access)
    {
        $clientId = $this->authorizeProject($project, $access);
        $validated = $request->validate([
            'response' => ['required', 'in:Approve,Reject,Request Clarification'],
            'option_id' => ['nullable', 'integer'],
            'comments' => ['nullable', 'string', 'max:5000'],
            'confirm_response' => ['accepted'],
        ]);
        if ($validated['response'] === 'Approve' && empty($validated['option_id'])) {
            throw ValidationException::withMessages(['option_id' => 'Select an option to approve.']);
        }
        if ($validated['response'] !== 'Approve' && blank($validated['comments'] ?? null)) {
            throw ValidationException::withMessages(['comments' => 'Please explain your rejection or clarification request.']);
        }
        DB::transaction(function () use ($project, $decision, $clientId, $validated, $request, $access) {
            // Lock decision to prevent two simultaneous responses and withdrawal races.
            $item = DB::table('client_decisions')->where('id', $decision)
                ->where('project_id', $project->id)->lockForUpdate()->first();
            abort_unless($item && $item->status === 'Published' && $item->published_at !== null
                && $item->withdrawn_at === null, 409, 'This decision is no longer accepting responses.');
            // Recheck grant at write time.
            $client = Auth::guard('client')->user();
            $access->assertAllowed($client, (int) $project->id);
            abort_if(DB::table('client_decision_responses')->where('decision_id', $decision)
                ->whereIn('response', ['Approve', 'Reject'])->exists(),
                409, 'This decision already has a final response.');
            abort_if(DB::table('client_decision_clarifications')->where('decision_id', $decision)
                ->whereNull('replied_at')->exists(), 409, 'Please wait for Ravion Homes to answer your question.');
            if ($validated['response'] === 'Request Clarification') {
                $clarificationId = DB::table('client_decision_clarifications')->insertGetId([
                    'decision_id' => $decision, 'client_user_id' => $clientId,
                    'question' => $validated['comments'], 'client_ip_address' => $request->ip(),
                    'requested_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('client_decision_events')->insert([
                    'decision_id' => $decision, 'actor_guard' => 'client', 'actor_id' => $clientId,
                    'action' => 'clarification_requested',
                    'details' => json_encode(['clarification_id' => $clarificationId]),
                    'ip_address' => $request->ip(), 'created_at' => now(),
                ]);
                return;
            }
            $optionId = null;
            if ($validated['response'] === 'Approve') {
                $optionId = (int) $validated['option_id'];
                abort_unless(DB::table('client_decision_options')->where('decision_id', $decision)
                    ->where('id', $optionId)->exists(), 422, 'The selected option is invalid.');
            }
            $responseId = DB::table('client_decision_responses')->insertGetId([
                'decision_id' => $decision,
                'client_user_id' => $clientId,
                'option_id' => $optionId,
                'response' => $validated['response'],
                'comments' => $validated['comments'] ?? null,
                'ip_address' => $request->ip(),
                'responded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('client_decisions')->where('id', $decision)
                ->update(['status' => 'Responded', 'updated_at' => now()]);
            DB::table('client_decision_events')->insert([
                'decision_id' => $decision,
                'actor_guard' => 'client',
                'actor_id' => $clientId,
                'action' => 'client_responded',
                'details' => json_encode(['response_id' => $responseId, 'response' => $validated['response'], 'option_id' => $optionId]),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        });
        return redirect()->route('client.decisions.show', ['project' => $project->id, 'decision' => $decision])
            ->with('success', $validated['response'] === 'Request Clarification' ? 'Your clarification request has been sent to Ravion Homes.' : 'Your final response has been recorded successfully.');
    }

    public function download(Project $project, int $decision, int $file, ClientProjectAccess $access)
    {
        $this->authorizeProject($project, $access);
        abort_unless($this->visible((int) $project->id)->where('id', $decision)->exists(), 404);
        $attachment = DB::table('client_decision_files')->where('decision_id', $decision)
            ->where('id', $file)->first();
        abort_unless($attachment && $attachment->disk === 'local', 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);
        return Storage::disk('local')->download($attachment->path, $attachment->original_name, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
