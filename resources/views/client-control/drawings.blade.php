@extends('client-control.project-layout', ['workspaceProject' => $project])
@section('title','Construction Drawings')
@section('project-content')
@include('client-control.partials.drawing-ui')
<div class="dr-client">

<div class="eyebrow">{{$project->project_name}}</div><h1 class="page-title">Construction Drawings</h1><p class="lead">Current published drawings for your project.</p>
@if(session('success'))<p class="card" role="status">{{session('success')}}</p>@endif
@if($errors->any())<div class="card" role="alert">@foreach($errors->all() as $error)<p>{{$error}}</p>@endforeach</div>@endif
<div class="dr-metrics"><div class="dr-metric">Published drawings<strong>{{$publications->count()}}</strong></div><div class="dr-metric">Awaiting your approval<strong>{{$publications->filter(fn($p)=>$p->client_approval_required && !$responses->has($p->id))->count()}}</strong></div><div class="dr-metric">Approved by you<strong>{{$responses->where('response','Approved')->count()}}</strong></div></div>
<div class="grid">@forelse($publications as $p)<article class="card"><div class="dr-flex"><a target="_blank" rel="noopener" href="{{route('client.drawings.file',[$project->id,$p->id,'preview'])}}"><img class="dr-thumb" loading="lazy" alt="Drawing thumbnail" src="{{route('client.drawings.file',[$project->id,$p->id,'preview'])}}?thumbnail=1"></a><div><span class="tag">{{$p->metadata['category']}}</span><h2 class="section-title" style="margin-top:12px">{{$p->metadata['title']}}</h2><p class="muted">{{$p->metadata['drawing_number']}} · {{$p->metadata['revision_reference']}}</p><p>{{$p->metadata['purpose']}} · {{$p->metadata['drawing_date']}}</p><p>{{$p->metadata['floor'] ?? ''}}</p><p>{{$p->metadata['client_description'] ?? ''}}</p><p><a class="dr-primary" target="_blank" rel="noopener" href="{{route('client.drawings.file',[$project->id,$p->id,'preview'])}}">View drawing</a> @if($p->client_download_allowed)<a class="tag" href="{{route('client.drawings.file',[$project->id,$p->id,'download'])}}">Download original</a>@else <span class="muted">View only</span>@endif</p></div></div>
@if($p->client_approval_required)
<hr style="border:0;border-top:1px solid #dbe5ef;margin:18px 0">
@if($answer=$responses->get($p->id))
<p><strong>Your response: {{$answer->response}}</strong></p><p class="muted">{{$answer->responded_at}}</p><p style="white-space:pre-wrap">{{$answer->remarks}}</p>
@else
<p><strong>Awaiting your approval</strong></p>
@if($p->client_approval_due_date)<p>Requested by: {{$p->client_approval_due_date}}</p>@endif
<form method="POST" action="{{route('client.drawings.respond',[$project->id,$p->id])}}">@csrf
<label for="remarks-{{$p->id}}">Remarks (required when requesting changes)</label><textarea id="remarks-{{$p->id}}" name="remarks" maxlength="5000" rows="3" style="display:block;width:100%;box-sizing:border-box;border:1px solid #cbd9e8;border-radius:8px;padding:10px;margin:8px 0"></textarea>
<label style="display:block;margin:12px 0"><input type="checkbox" name="confirm_response" value="1" required> I have reviewed this revision and confirm my response.</label>
<button class="approve" name="response" value="Approved">Approve Drawing</button> <button class="changes" name="response" value="Changes Requested">Request Changes</button>
</form><p class="muted">Your response is final for this publication. It is not technical authorization for construction.</p>
@endif
@else<p class="muted">Information only — no response required.</p>@endif
</article>@empty<p class="empty">No drawings have been published yet.</p>@endforelse</div>
</div>
@endsection
