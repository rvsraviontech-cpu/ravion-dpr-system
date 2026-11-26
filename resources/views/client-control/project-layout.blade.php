@extends('client-control.layout')
@section('content')
    @include('client-control.partials.project-banner', ['project' => $workspaceProject])
    @include('client-control.partials.project-navigation', ['project' => $workspaceProject])
    @yield('project-content')
@endsection
