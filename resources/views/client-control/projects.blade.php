@extends('client-control.layout')
@section('title', 'My Projects')
@section('content')
<div class="eyebrow">Your workspace</div>
<h1 class="page-title">My Projects</h1>
<p class="lead">Select a project to view information approved for your account.</p>
<div class="grid">
@forelse($projects as $project)
<a class="card project-card" href="{{ route('client.projects.show', $project->id) }}">
<span class="tag">Approved access</span>
<div class="project-name">{{ $project->project_name }}</div>
<div class="muted">Project {{ $project->project_code }}</div>
<div style="margin-top:22px;font-size:13px;font-weight:700;color:#285a78">Open project &rarr;</div>
</a>
@empty
<div class="card"><h2 class="section-title">No active project access</h2><p class="empty">Your account currently has no approved, active project grant. Contact the Ravion project administrator if you believe this is incorrect.</p></div>
@endforelse
</div>
@endsection
