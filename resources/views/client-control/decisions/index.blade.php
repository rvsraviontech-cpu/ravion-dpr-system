@extends('client-control.layout')
@section('title', 'My Decisions')
@section('content')
<a class="back" href="{{ route('client.projects.show', ['project' => $project->id]) }}">← Project overview</a>
<div class="project-header"><div><div class="eyebrow">{{ $project->project_name }}</div><h1 class="page-title">My Decisions</h1><p class="lead">Review requests published by Ravion Homes and track your responses.</p></div></div>
@include('client-control.partials.project-navigation', ['project' => $project])
<style>
.rv-dec-list{display:grid;gap:13px}.rv-dec-row{display:block;padding:20px 22px;background:white;border:1px solid #dce7f1;border-radius:15px}.rv-dec-row:hover{border-color:#7fa8c6}.rv-dec-meta{display:flex;gap:10px;flex-wrap:wrap;align-items:center;color:#61788e;font-size:12px}.rv-dec-title{font-size:18px;font-weight:750;color:#102448;margin:9px 0}.rv-dec-empty{padding:28px}.rv-dec-status{background:#e8f3f7;border-radius:7px;padding:5px 9px;font-weight:700;color:#245e76}.rv-dec-due{font-weight:650}
</style>
<div class="rv-dec-list">
@forelse($decisions as $decision)
<a class="rv-dec-row" href="{{ route('client.decisions.show', ['project' => $project->id, 'decision' => $decision->id]) }}">
<div class="rv-dec-meta"><span>{{ $decision->reference }}</span><span>·</span><span>{{ $decision->category }}</span><span class="rv-dec-status">{{ in_array($decision->id, $respondedIds) ? 'Your response submitted' : ($decision->status === 'Published' ? 'Awaiting your response' : $decision->status) }}</span></div>
<div class="rv-dec-title">{{ $decision->title }}</div>
<div class="rv-dec-meta"><span class="rv-dec-due">Due: {{ $decision->response_due_at ?? 'Not specified' }}</span><span>·</span><span>Published: {{ $decision->published_at }}</span></div>
</a>
@empty
<div class="card rv-dec-empty"><h2 class="section-title">No decisions published yet</h2><p class="muted">When your project team publishes a decision request, it will appear here.</p></div>
@endforelse
</div>
<div style="margin-top:20px">{{ $decisions->links() }}</div>
@endsection
