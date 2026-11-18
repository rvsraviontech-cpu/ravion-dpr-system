
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    <header>
        <p class="text-sm font-semibold text-teal-700 uppercase tracking-wide">
            Client Control / Work Progress
        </p>
        <h1 class="text-3xl font-bold text-slate-900 mt-2">
            Project Work Progress
        </h1>
        <p class="text-slate-500 mt-2">
            Manage project-specific stages, tasks and completion progress.
            All information remains internal until approved and published.
        </p>
    </header>
    <a href="{{ route('client-control.admin.progress.master.index') }}">Common Stages & Tasks</a>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($projects as $project)
            <a href="{{ route('client-control.admin.progress.show', $project->id) }}"
               class="block bg-white rounded-2xl border border-slate-200 p-6 hover:border-teal-500 hover:shadow-md transition">

                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold tracking-wide text-teal-700">
                        {{ $project->project_code }}
                    </span>
                    <span class="text-xs rounded-full bg-slate-100 text-slate-600 px-3 py-1">
                        {{ $project->status }}
                    </span>
                </div>

                <h2 class="text-lg font-bold text-slate-900 mt-4">
                    {{ $project->project_name }}
                </h2>

                <p class="text-sm text-slate-500 mt-3">
                    Open stage and task management
                </p>

                <div class="border-t border-slate-100 mt-5 pt-4 text-sm font-semibold text-teal-700">
                    Manage Work Progress &rarr;
                </div>
            </a>
        @empty
            <p class="text-slate-500">No projects available.</p>
        @endforelse
    </div>
</div>
@endsection
