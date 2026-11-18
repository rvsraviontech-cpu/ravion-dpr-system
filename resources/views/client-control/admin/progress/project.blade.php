
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    <a href="{{ route('client-control.admin.progress.index') }}"
       class="text-sm font-semibold text-teal-700">
        &larr; All Projects
    </a>

    <header>
        <p class="text-sm font-semibold text-teal-700 uppercase">
            Client Control / Work Progress
        </p>
        <h1 class="text-3xl font-bold text-slate-900 mt-2">
            {{ $projectRecord->project_name }}
        </h1>
        <p class="text-slate-500 mt-1">
            {{ $projectRecord->project_code }} · Internal progress management
        </p>
    </header>

    <a href="{{ route('client-control.admin.progress.master.preview', $projectRecord->id) }}">Apply Common Stages</a>
<a href="{{ route('client-control.admin.progress.snapshots.index', $projectRecord->id) }}" class="ml-3 text-teal-700 font-semibold">Review & Publish Progress</a>
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 p-4 text-red-800">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="bg-white border border-slate-200 rounded-2xl p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">Calculated project progress</p>
                <h2 class="text-4xl font-bold text-slate-900 mt-1">
                    {{ number_format($overall, 2) }}%
                </h2>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-500">Assigned stage weightage</p>
                <p class="text-xl font-bold text-slate-800">
                    {{ number_format($totalWeight, 2) }} / 100%
                </p>
            </div>
        </div>

        <div class="h-3 bg-slate-100 rounded-full overflow-hidden mt-5">
            <div class="h-full bg-teal-600 rounded-full"
                 style="width: {{ min(100, max(0, $overall)) }}%"></div>
        </div>

        @if(abs($totalWeight - 100) > 0.001)
            <p class="text-sm text-amber-700 mt-4">
                Draft baseline: stage weightages must total 100% before
                progress can be submitted for approval.
            </p>
        @endif

        <p class="text-xs text-slate-500 mt-3">
            Internal calculation only. Not published to clients.
        </p>
    </section>

    <section class="bg-white border border-slate-200 rounded-2xl p-6">
        <h2 class="text-xl font-bold text-slate-900 mb-4">
            Add Project Stage
        </h2>

        <form method="POST"
              action="{{ route('client-control.admin.progress.stages.store', $projectRecord->id) }}"
              class="grid gap-4 md:grid-cols-4">
            @csrf

            <input name="stage_name" required maxlength="255"
                   placeholder="Stage name"
                   class="md:col-span-2 rounded-xl border-slate-300">

            <input name="weightage" type="number" min="0" max="100"
                   step="0.0001" required placeholder="Weightage %"
                   onwheel="this.blur()"
                   class="rounded-xl border-slate-300">

            <button type="submit"
                    class="rounded-xl bg-teal-700 px-5 py-3 text-white font-semibold">
                Add Stage
            </button>

            <textarea name="description" rows="2" maxlength="5000"
                      placeholder="Optional stage description"
                      class="md:col-span-4 rounded-xl border-slate-300"></textarea>
        </form>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-900">Construction Stages</h2>
            <span class="text-sm text-slate-500">{{ $stages->count() }} stages · Click a stage to expand</span>
        </div>
        @forelse($stages as $stage)
            <details class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <summary class="cursor-pointer list-none p-4 sm:p-5 select-none [&::-webkit-details-marker]:hidden">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-sm font-bold text-teal-800">
                            {{ $loop->iteration }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $stage->stage_name }}</h2>
                                <span class="text-xs text-slate-500">Weight: {{ number_format($stage->weightage, 2) }}%</span>
                                <span class="text-xs text-slate-500">{{ $tasks->get($stage->id, collect())->count() }} tasks</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden mt-2">
                                <div class="h-full bg-teal-600 rounded-full" style="width: {{ min(100, max(0, $stage->progress)) }}%"></div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-lg sm:text-xl font-bold text-teal-700">{{ number_format($stage->progress, 1) }}%</p>
                            <p class="text-xs text-slate-500">Complete</p>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-slate-500 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                    </div>
                </summary>
                <div class="border-t border-slate-100 p-4 sm:p-6 space-y-4">
                    @if($stage->description)
                        <p class="text-sm text-slate-600">{{ $stage->description }}</p>
                    @endif
                    <div class="grid gap-3 lg:grid-cols-2">
                        <details class="rounded-xl border border-slate-200 bg-white p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-teal-800">Edit Stage Details & Weightage</summary>
                            <form method="POST" action="{{ route('client-control.admin.progress.stages.update', [$projectRecord->id, $stage->id]) }}" class="mt-4 space-y-3">
                                @csrf
                                @method('PATCH')
                                <label class="block text-xs font-medium text-slate-600">Stage name
                                    <input type="text" name="stage_name" value="{{ $stage->stage_name }}" maxlength="255" required class="mt-1 w-full rounded-xl border-slate-300">
                                </label>
                                <label class="block text-xs font-medium text-slate-600">Stage weightage (%)
                                    <input type="number" name="weightage" value="{{ $stage->weightage }}" min="0" max="100" step="0.0001" required onwheel="this.blur()" class="mt-1 w-full rounded-xl border-slate-300">
                                </label>
                                <label class="block text-xs font-medium text-slate-600">Description
                                    <textarea name="description" maxlength="5000" rows="2" class="mt-1 w-full rounded-xl border-slate-300">{{ $stage->description }}</textarea>
                                </label>
                                <p class="text-xs text-amber-700">Changing weightage can recalculate current progress. Previously published snapshots remain unchanged.</p>
                                <button type="submit" class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Save Stage Changes</button>
                            </form>
                        </details>
                        <details class="rounded-xl border border-red-200 bg-red-50/40 p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-red-700">Archive / Remove Stage</summary>
                            <form method="POST" action="{{ route('client-control.admin.progress.stages.archive', [$projectRecord->id, $stage->id]) }}" class="mt-4 space-y-3">
                                @csrf
                                <p class="text-sm text-slate-700">Remove this stage from active progress and weightage calculations? Its tasks and audit history will be retained. Published snapshots will not change.</p>
                                <label class="flex items-start gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="confirm_archive" value="1" required class="mt-1 rounded border-slate-300 text-red-700">
                                    <span>I understand that archiving this stage changes the current project baseline.</span>
                                </label>
                                <button type="submit" onclick="return confirm('Archive this stage from active project progress?')" class="rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700">Archive Stage</button>
                            </form>
                        </details>
                    </div>
                    <h3 class="font-bold text-slate-800">Stage Tasks</h3>

                    @forelse($tasks->get($stage->id, collect()) as $task)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 p-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800">
                                    {{ $task->task_name }}
                                </p>
                                @if($task->description)
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $task->description }}
                                    </p>
                                @endif
                            </div>

                            <form method="POST"
                                  action="{{ route('client-control.admin.progress.tasks.progress', [$projectRecord->id, $stage->id, $task->id]) }}"
                                  class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')

                                <select name="completion_percentage"
                                        class="rounded-xl border-slate-300 text-sm">
                                    @for($percentage = 0; $percentage <= 100; $percentage += 10)
                                        <option value="{{ $percentage }}"
                                            @selected((int)$task->completion_percentage === $percentage)>
                                            {{ $percentage }}%
                                        </option>
                                    @endfor
                                </select>

                                <button type="submit"
                                        class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-semibold text-white">
                                    Save
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">
                            No tasks added to this stage yet.
                        </p>
                    @endforelse

                    <form method="POST"
                          action="{{ route('client-control.admin.progress.tasks.store', [$projectRecord->id, $stage->id]) }}"
                          class="grid gap-3 md:grid-cols-4 pt-4 border-t border-slate-100">
                        @csrf

                        <input name="task_name" required maxlength="255"
                               placeholder="New task name"
                               class="md:col-span-2 rounded-xl border-slate-300">

                        <input name="description" maxlength="5000"
                               placeholder="Description (optional)"
                               class="rounded-xl border-slate-300">

                        <button type="submit"
                                class="rounded-xl border border-teal-700 text-teal-700 px-4 py-3 font-semibold">
                            + Add Task
                        </button>
                    </form>
                </div>
            </details>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                <p class="text-slate-600">
                    No stages configured. Add your first project stage above.
                </p>
            </div>
        @endforelse
    </section>

    @if(isset($archivedStages) && $archivedStages->isNotEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <details>
                <summary class="cursor-pointer text-lg font-bold text-slate-800">Archived Stages ({{ $archivedStages->count() }})</summary>
                <div class="mt-4 space-y-3">
                    @foreach($archivedStages as $archivedStage)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $archivedStage->stage_name }}</p>
                                <p class="text-xs text-slate-500">Previously assigned weight: {{ number_format($archivedStage->weightage, 2) }}%. Tasks and history retained.</p>
                            </div>
                            <form method="POST" action="{{ route('client-control.admin.progress.stages.restore', [$projectRecord->id, $archivedStage->id]) }}">
                                @csrf
                                <button type="submit" class="rounded-xl border border-teal-700 px-4 py-2 text-sm font-semibold text-teal-700">Restore Stage</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </details>
        </section>
    @endif

    <section class="bg-white border border-slate-200 rounded-2xl p-6">
        <h2 class="text-xl font-bold text-slate-900 mb-4">
            Recent Progress History
        </h2>

        <div class="space-y-3">
            @forelse($history as $entry)
                <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 pb-3 text-sm">
                    <span class="font-medium text-slate-700">
                        {{ str_replace(['.', '_'], ' ', $entry->action) }}
                    </span>
                    <span class="text-slate-500">
                        {{ $entry->created_at }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No changes recorded yet.</p>
            @endforelse
        </div>
    </section>
</div>

<style>
input[type="number"] {
    appearance: textfield;
    -moz-appearance: textfield;
}
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
@endsection
