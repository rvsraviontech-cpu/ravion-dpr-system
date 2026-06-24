@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">
    <form action="{{ route('projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('projects.partials.form', [
            'project' => $project,
            'title' => 'Edit Project',
            'subtitle' => $project->project_code . ' — ' . $project->project_name,
            'buttonText' => 'Update Project'
        ])
    </form>
</div>

@endsection