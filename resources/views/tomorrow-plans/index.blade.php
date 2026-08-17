@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2 sm:text-sm';
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Tomorrow Plans</h1>
            <p class="mt-1 text-gray-500">
                Manage tomorrow work plans, resources, dependencies and approvals.
            </p>
        </div>

        <a href="{{ route('tomorrow-plans.create') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-2.5">
            + Add Tomorrow Plan
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6" x-data="{ filtersOpen: false }">
        <button type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-3 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-left shadow-sm lg:hidden">
            <div>
                <div class="text-sm font-bold text-gray-800">Filters</div>
                <div class="text-xs text-gray-500">Planned date, project and status</div>
            </div>

            <svg class="h-5 w-5 text-gray-500 transition-transform"
                 :class="filtersOpen ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="filtersOpen" x-cloak class="lg:hidden">
            <form method="GET"
                  action="{{ route('tomorrow-plans.index') }}"
                  class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Planned Date</label>
                        <input type="date"
                               name="planned_date"
                               value="{{ request('planned_date') }}"
                               class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Project</label>
                        <select name="project_id" class="{{ $inputClass }}">
                            <option value="">All Projects</option>

                            @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select name="status" class="{{ $inputClass }}">
                            <option value="">All Status</option>
                            <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Submitted" {{ request('status') === 'Submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                            Filter
                        </button>

                        <a href="{{ route('tomorrow-plans.index') }}"
                           class="rounded-lg bg-gray-500 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-gray-600">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="hidden lg:block">
            <form method="GET"
                  action="{{ route('tomorrow-plans.index') }}"
                  class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Planned Date</label>
                        <input type="date"
                               name="planned_date"
                               value="{{ request('planned_date') }}"
                               class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Project</label>
                        <select name="project_id" class="{{ $inputClass }}">
                            <option value="">All Projects</option>

                            @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select name="status" class="{{ $inputClass }}">
                            <option value="">All Status</option>
                            <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Submitted" {{ request('status') === 'Submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Filter
                        </button>

                        <a href="{{ route('tomorrow-plans.index') }}"
                           class="rounded-lg bg-gray-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-600">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="space-y-4 lg:hidden">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Tomorrow Plans</h2>
                <p class="text-xs text-gray-500">
                    {{ $tomorrowPlans->total() }} matching plan{{ $tomorrowPlans->total() === 1 ? '' : 's' }}
                </p>
            </div>

            <div class="text-xs text-gray-500">
                {{ $tomorrowPlans->firstItem() ?? 0 }}–{{ $tomorrowPlans->lastItem() ?? 0 }}
            </div>
        </div>

        @forelse($tomorrowPlans as $index => $plan)
            @php
                $priorityClass = match($plan->priority) {
                    'Critical' => 'bg-red-100 text-red-800',
                    'Urgent' => 'bg-orange-100 text-orange-800',
                    default => 'bg-blue-100 text-blue-800',
                };

                $statusClass = match($plan->status) {
                    'Approved' => 'bg-green-100 text-green-800',
                    'Submitted' => 'bg-orange-100 text-orange-800',
                    default => 'bg-yellow-100 text-yellow-800',
                };

                $location = collect([
                    $plan->block?->name,
                    $plan->floor?->name,
                    $plan->getRelation('unit')?->name,
                    $plan->room?->name,
                    $plan->subspace?->name,
                ])->filter()->implode(' › ');
            @endphp

            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-[#0F2A52] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-blue-100">
                                {{ $plan->planned_date ?? '-' }}
                            </div>

                            <h3 class="mt-1 truncate text-base font-bold">
                                {{ $plan->project?->project_name ?? '-' }}
                            </h3>

                            @if($location)
                                <div class="mt-1 line-clamp-2 text-xs text-blue-100">
                                    {{ $location }}
                                </div>
                            @endif
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $priorityClass }}">
                            {{ $plan->priority }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Planned Activity</div>
                        <div class="mt-1 font-semibold text-gray-800">
                            {{ $plan->activity?->activity_name ?? '-' }}
                        </div>
                        <div class="mt-2 text-sm font-bold text-[#0F2A52]">
                            {{ rtrim(rtrim(number_format((float) $plan->planned_quantity, 2, '.', ''), '0'), '.') }}
                            {{ $plan->unit ?? '' }}
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-blue-50 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Planned Labour</div>
                            <div class="mt-1 text-lg font-bold text-blue-800">
                                {{ $plan->planned_labour ?? 0 }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-slate-50 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Status</div>
                            <div class="mt-1">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $plan->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($plan->contractor)
                        <div class="mt-3 rounded-xl border border-gray-200 px-3 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Contractor</div>
                            <div class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $plan->contractor->contractor_name }}
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('tomorrow-plans.show', $plan) }}"
                           class="rounded-xl bg-[#0F2A52] px-4 py-3 text-center text-sm font-semibold text-white">
                            View
                        </a>

                        @if($plan->status === 'Draft')
                            <a href="{{ route('tomorrow-plans.edit', $plan) }}"
                               class="rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-semibold text-white">
                                Edit
                            </a>
                        @else
                            <div class="flex items-center justify-center rounded-xl bg-gray-100 px-4 py-3 text-xs font-semibold text-gray-500">
                                {{ $plan->status }}
                            </div>
                        @endif
                    </div>

                    @if($plan->status === 'Draft')
                        <form method="POST"
                              action="{{ route('tomorrow-plans.submit', $plan) }}"
                              class="mt-3">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm('Submit this tomorrow plan for approval?')"
                                    class="w-full rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white">
                                Submit Plan
                            </button>
                        </form>
                    @endif

                    @if($plan->status === 'Submitted'
                        && in_array(auth()->user()->role->name, ['Admin','PMO','DGM']))
                        <form method="POST"
                              action="{{ route('tomorrow-plans.approve', $plan) }}"
                              class="mt-3">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm('Approve this tomorrow plan?')"
                                    class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">
                                Approve Plan
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center shadow-sm">
                <div class="text-base font-semibold text-gray-700">No tomorrow plans found</div>
                <p class="mt-2 text-sm text-gray-500">Adjust the filters or create the first plan.</p>

                <a href="{{ route('tomorrow-plans.create') }}"
                   class="mt-4 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">
                    + Add Tomorrow Plan
                </a>
            </div>
        @endforelse

        @if($tomorrowPlans->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $tomorrowPlans->links() }}
            </div>
        @endif
    </div>

    {{-- Desktop Table --}}
    <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Project</th>
                        <th class="p-3 text-left">Location</th>
                        <th class="p-3 text-left">Activity</th>
                        <th class="p-3 text-center">Qty</th>
                        <th class="p-3 text-left">Unit</th>
                        <th class="p-3 text-center">Labour</th>
                        <th class="p-3 text-left">Priority</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($tomorrowPlans as $index => $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">{{ $tomorrowPlans->firstItem() + $index }}</td>
                            <td class="whitespace-nowrap p-3">{{ $plan->planned_date ?? '-' }}</td>
                            <td class="whitespace-nowrap p-3">{{ $plan->project?->project_name ?? '-' }}</td>

                            <td class="min-w-[220px] p-3">
                                {{ collect([
                                    $plan->block?->name,
                                    $plan->floor?->name,
                                    $plan->getRelation('unit')?->name,
                                    $plan->room?->name,
                                    $plan->subspace?->name,
                                ])->filter()->implode(' / ') ?: '-' }}
                            </td>

                            <td class="min-w-[180px] p-3">{{ $plan->activity?->activity_name ?? '-' }}</td>

                            <td class="p-3 text-center font-bold">
                                {{ rtrim(rtrim(number_format((float) $plan->planned_quantity, 2, '.', ''), '0'), '.') }}
                            </td>

                            <td class="p-3">{{ $plan->unit ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $plan->planned_labour ?? 0 }}</td>

                            <td class="p-3">
                                <span class="rounded px-2 py-1 text-xs
                                    {{ $plan->priority === 'Normal' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $plan->priority === 'Urgent' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $plan->priority === 'Critical' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $plan->priority }}
                                </span>
                            </td>

                            <td class="p-3">
                                <span class="rounded px-2 py-1 text-xs
                                    {{ $plan->status === 'Draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $plan->status === 'Submitted' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $plan->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ $plan->status }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('tomorrow-plans.show', $plan) }}"
                                       class="rounded bg-gray-700 px-3 py-1 text-white hover:bg-gray-800">
                                        View
                                    </a>

                                    @if($plan->status === 'Draft')
                                        <a href="{{ route('tomorrow-plans.edit', $plan) }}"
                                           class="rounded bg-yellow-500 px-3 py-1 text-white hover:bg-yellow-600">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('tomorrow-plans.submit', $plan) }}" class="inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                                Submit
                                            </button>
                                        </form>
                                    @endif

                                    @if($plan->status === 'Submitted'
                                        && in_array(auth()->user()->role->name, ['Admin','PMO','DGM']))
                                        <form method="POST" action="{{ route('tomorrow-plans.approve', $plan) }}" class="inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">
                                                Approve
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-6 text-center text-gray-500">
                                No tomorrow plans found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tomorrowPlans->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $tomorrowPlans->links() }}
            </div>
        @endif
    </div>

</div>

@endsection
