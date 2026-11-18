@extends('client-control.layout')
@section('title', 'Decision Details')
@section('content')
<a class="back" href="{{ route('client.decisions.index', ['project' => $project->id]) }}">← All decisions</a>
<div class="eyebrow">{{ $project->project_name }} · {{ $item->reference }}</div>
<h1 class="page-title">{{ $item->title }}</h1>
<p class="lead">{{ $item->category }} · Response due {{ $item->response_due_at ?? 'not specified' }}</p>
@include('client-control.partials.project-navigation', ['project' => $project])
<style>
.rv-d-stack{display:grid;gap:16px;max-width:920px}.rv-d-copy{white-space:pre-wrap;line-height:1.7;color:#334e66}.rv-d-option{display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-top:1px solid #e1eaf3}.rv-d-option input{margin-top:5px;accent-color:#145a79}.rv-d-option strong{display:block;color:#102448}.rv-d-option p{color:#637b8f;margin:5px 0 0;font-size:13px;line-height:1.5}.rv-d-field{width:100%;border:1px solid #cbd9e7;border-radius:10px;padding:12px;font:inherit;min-height:100px}.rv-d-select{width:100%;border:1px solid #cbd9e7;border-radius:10px;padding:12px;font:inherit}.rv-d-btn{background:#153c55;color:#fff;border:0;border-radius:10px;padding:12px 20px;font-weight:750}.rv-d-alert{padding:14px;border-radius:10px;background:#e8f6f3;color:#176051}.rv-d-error{color:#ae3030;font-size:13px;margin-top:5px}.rv-d-file{display:block;padding:10px 0;border-top:1px solid #e1eaf3;color:#1a6a80;text-decoration:underline}.rv-d-note{background:#f5f8fc;padding:14px;border-radius:10px;color:#536b7e;font-size:13px;line-height:1.6}
</style>
<div class="rv-d-stack">
@if(session('success'))<div class="rv-d-alert" role="status">{{ session('success') }}</div>@endif
<div class="card"><h2 class="section-title">Decision request</h2><div class="rv-d-copy">{{ $item->description }}</div>
@if($item->has_cost_impact || $item->has_time_impact)
<div class="rv-d-note" style="margin-top:16px"><strong>Cost / schedule implications</strong><div class="rv-d-copy">{{ $item->impact_details }}</div></div>
@endif
</div>
<div class="card"><h2 class="section-title">Available options</h2>
@forelse($options as $option)
<div class="rv-d-option"><div><strong>{{ $option->label }}</strong>@if($option->details)<p>{{ $option->details }}</p>@endif</div></div>
@empty<p class="muted">No options provided.</p>@endforelse
</div>
<div class="card"><h2 class="section-title">Supporting documents</h2>
@forelse($files as $file)
<a class="rv-d-file" href="{{ route('client.decisions.files.download', ['project' => $project->id, 'decision' => $item->id, 'file' => $file->id]) }}">{{ $file->original_name }}</a>
@empty<p class="muted">No supporting files attached.</p>@endforelse
</div>
<div class="card"><h2 class="section-title">Clarification conversation</h2>
@forelse($clarifications as $clarification)
<div style="padding:14px 0;border-top:1px solid #e1eaf3">
<p><strong>Client question</strong> · {{ $clarification->requested_at }}</p>
<div class="rv-d-copy">{{ $clarification->question }}</div>
@if($clarification->replied_at)
<div class="rv-d-note" style="margin-top:12px"><strong>Ravion Homes reply</strong> · {{ $clarification->replied_at }}<div class="rv-d-copy">{{ $clarification->reply }}</div></div>
@else
<p class="muted">Awaiting a reply from Ravion Homes.</p>
@endif
</div>
@empty<p class="muted">No clarification questions have been raised.</p>@endforelse
</div>
@if($responses->isNotEmpty())
<div class="card"><h2 class="section-title">Your recorded response</h2>
@foreach($responses as $response)
<p><strong>{{ $response->response }}</strong> · {{ $response->responded_at }}</p>
@if($response->option_id)
@php $chosen = $options->firstWhere('id', $response->option_id); @endphp
@if($chosen)<p>Selected option: {{ $chosen->label }}</p>@endif
@endif
@if($response->comments)<div class="rv-d-copy">{{ $response->comments }}</div>@endif
<p class="muted">{{ $response->acknowledged_at ? 'Acknowledged by Ravion Homes' : 'Awaiting internal acknowledgement' }}</p>
@endforeach
</div>
@elseif($item->status === 'Published' && $pendingClarification)
<div class="card"><p class="muted">Your clarification request is awaiting a reply. You can submit your final decision after Ravion Homes responds.</p></div>
@elseif($item->status === 'Published')
<div class="card"><h2 class="section-title">Submit your decision</h2><p class="muted">Choose your response carefully. Approval or rejection is final. A clarification request can be answered before you make a final decision.</p>
<form method="POST" action="{{ route('client.decisions.respond', ['project' => $project->id, 'decision' => $item->id]) }}" onsubmit="return confirm(document.getElementById('response').value === 'Request Clarification' ? 'Send this clarification request to Ravion Homes?' : 'Submit this decision as your final response?');">
@csrf
<label for="response"><strong>Your response</strong></label>
<select id="response" name="response" class="rv-d-select" required style="margin:9px 0 16px" onchange="document.getElementById('approval-option').hidden=this.value!=='Approve';document.getElementById('approval-option-select').required=this.value==='Approve';document.getElementById('response-comments').required=this.value!=='Approve' && this.value!=='';">
<option value="">Select a response</option>
<option value="Approve" @selected(old('response')==='Approve')>Approve an option</option>
<option value="Reject" @selected(old('response')==='Reject')>Reject</option>
<option value="Request Clarification" @selected(old('response')==='Request Clarification')>Request clarification</option>
</select>
@error('response')<p class="rv-d-error">{{ $message }}</p>@enderror
<div id="approval-option" @if(old('response')!=='Approve') hidden @endif>
<label for="approval-option-select"><strong>Option to approve</strong></label>
<select id="approval-option-select" name="option_id" class="rv-d-select" style="margin:9px 0 16px" @if(old('response')==='Approve') required @endif>
<option value="">Select an option</option>
@foreach($options as $option)
<option value="{{ $option->id }}" @selected(old('option_id')==$option->id)>{{ $option->label }}</option>
@endforeach
</select>
@error('option_id')<p class="rv-d-error">{{ $message }}</p>@enderror
</div>
<label for="response-comments"><strong>Remarks</strong> <span class="muted">(required for rejection or clarification)</span></label>
<textarea id="response-comments" name="comments" maxlength="5000" class="rv-d-field" style="margin:9px 0 16px" @if(in_array(old('response'),['Reject','Request Clarification'])) required @endif>{{ old('comments') }}</textarea>
@error('comments')<p class="rv-d-error">{{ $message }}</p>@enderror
<div class="rv-d-note" style="margin-bottom:15px">Your selection records your response to this project decision. It does not, by itself, authorize a contractual variation or additional charges.</div>
<label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:16px"><input type="checkbox" name="confirm_response" value="1" required style="margin-top:4px"><span>I confirm this is my intended response and understand it will be recorded.</span></label>
@error('confirm_response')<p class="rv-d-error">{{ $message }}</p>@enderror
<button class="rv-d-btn" type="submit">Submit response</button>
</form></div>
@else
<div class="card"><p class="muted">This decision is not accepting new responses.</p></div>
@endif
</div>
@endsection
