@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">
    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        @include('projects.partials.form', [
            'project' => null,
            'title' => 'Create Project',
            'subtitle' => 'ERP-grade project master setup',
            'buttonText' => 'Save Project'
        ])
    </form>
</div>

@endsection