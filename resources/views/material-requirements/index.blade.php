@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2 sm:text-sm';

    $roleName = auth()->user()->role?->name;

    $hasPermission = function (string $permission) use ($roleName): bool {
        if ($roleName === 'Admin') {
            return true;
        }

        return auth()->user()
            ->role
            ?->permissions()
            ->where('name', $permission)
            ->where('is_active', true)
            ->exists() ?? false;
    };

    $canCreate = $hasPermission('material_required.create');
    $canEdit = $hasPermission('material_required.edit');

    $canApprove = $hasPermission('material_required.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Material Requirements
            </h1>

            <p class="mt-1 text-gray-500">
                Manage project-wise material requirements, fulfilment and approval workflow.
            </p>
        </div>

        @if($canCreate)
            <a href="{{ route('material-requirements.create') }}"
               class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-2.5">
                + Add Requirement
            </a>
        @endif

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

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-2 xl:grid-cols-4 md:gap-4">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Draft Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-yellow-700">
                {{ $draftCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Submitted Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-orange-700">
                {{ $submittedCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Approved Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-green-700">
                {{ $approvedCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">
                Urgent Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-red-700">
                {{ $urgentCount }}
            </p>
        </div>

    </div>

    <div class="mb-6" x-data="{ filtersOpen: false }">
        <button type="button"
                @click="filtersOpen = !filtersOpen"
                class="mb-3 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-left shadow-sm lg:hidden">
            <div>
                <div class="text-sm font-bold text-gray-800">Filters</div>
                <div class="text-xs text-gray-500">Required date, project, priority, status and search</div>
            </div>
            <svg class="h-5 w-5 text-gray-500 transition-transform"
                 :class="filtersOpen ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="filtersOpen" x-cloak class="lg:hidden">
<form method="GET"
          action="{{ route('material-requirements.index') }}"
          class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Required Date
                </label>

                <input type="date"
                       name="required_date"
                       value="{{ request('required_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">

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
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Priority
                </label>

                <select name="priority"
                        class="{{ $inputClass }}">

                    <option value="">All Priorities</option>

                    @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                        <option value="{{ $priority }}"
                            {{ request('priority') === $priority ? 'selected' : '' }}>
                            {{ $priority }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">

                    <option value="">All Statuses</option>

                    @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $status)
                        <option value="{{ $status }}"
                            {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Project, material, brand, grade">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:py-2.5">
                    Filter
                </button>

                <a href="{{ route('material-requirements.index') }}"
                   class="w-full rounded-lg bg-gray-600 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
                    Clear
                </a>
            </div>

        </div>
    </form>
        </div>

        <div class="hidden lg:block">
<form method="GET"
          action="{{ route('material-requirements.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Required Date
                </label>

                <input type="date"
                       name="required_date"
                       value="{{ request('required_date') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Project
                </label>

                <select name="project_id"
                        class="{{ $inputClass }}">

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
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Priority
                </label>

                <select name="priority"
                        class="{{ $inputClass }}">

                    <option value="">All Priorities</option>

                    @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                        <option value="{{ $priority }}"
                            {{ request('priority') === $priority ? 'selected' : '' }}>
                            {{ $priority }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select name="status"
                        class="{{ $inputClass }}">

                    <option value="">All Statuses</option>

                    @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $status)
                        <option value="{{ $status }}"
                            {{ request('status') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Search
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="{{ $inputClass }}"
                       placeholder="Project, material, brand, grade">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('material-requirements.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>
    </form>
        </div>
    </div>

{{-- Mobile Material Requirements Register --}}
    <div class="space-y-4 lg:hidden">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Material Requirements</h2>
                <p class="text-xs text-gray-500">
                    {{ $requirements->total() }} matching requirement{{ $requirements->total() === 1 ? '' : 's' }}
                </p>
            </div>
            <div class="text-xs text-gray-500">
                {{ $requirements->firstItem() ?? 0 }}–{{ $requirements->lastItem() ?? 0 }}
            </div>
        </div>

        @forelse($requirements as $index => $requirement)
            @php
                $hasNewItems = $requirement->items->isNotEmpty();

                $priorityClasses = match($requirement->priority) {
                    'Urgent' => 'bg-red-100 text-red-800',
                    'High' => 'bg-orange-100 text-orange-800',
                    'Normal' => 'bg-blue-100 text-blue-800',
                    default => 'bg-gray-100 text-gray-800',
                };

                $statusClasses = match($requirement->status) {
                    'Approved' => 'bg-green-100 text-green-800',
                    'Submitted' => 'bg-blue-100 text-blue-800',
                    'Rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-yellow-100 text-yellow-800',
                };

                $itemCount = $hasNewItems ? $requirement->items->count() : 1;
                $primaryItem = $hasNewItems ? $requirement->items->first() : null;

                $primaryMaterial = $hasNewItems
                    ? ($primaryItem?->materialType?->material_type_name ?? '-')
                    : ($requirement->material?->material_name ?? '-');

                $primaryVariant = $hasNewItems
                    ? collect([
                        $primaryItem?->brand?->brand_name,
                        $primaryItem?->specification?->specification_name,
                        $primaryItem?->grade?->grade_name,
                    ])->filter()->implode(' • ')
                    : '';
            @endphp

            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-[#0F2A52] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-blue-100">
                                Required {{ $requirement->required_date?->format('d M Y') ?? '-' }}
                            </div>
                            <h3 class="mt-1 truncate text-base font-bold">
                                {{ $requirement->project?->project_name ?? '-' }}
                            </h3>
                            @if($requirement->block)
                                <div class="mt-1 truncate text-xs text-blue-100">{{ $requirement->block->name }}</div>
                            @endif
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $priorityClasses }}">
                            {{ $requirement->priority }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-blue-50 px-2 py-3 text-center">
                            <div class="text-lg font-bold text-blue-800">{{ $itemCount }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Items</div>
                        </div>

                        <div class="rounded-xl bg-green-50 px-2 py-3 text-center">
                            <div class="text-lg font-bold text-green-800">
                                {{ formatQuantity($requirement->total_fulfilled_quantity) }}
                            </div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-green-700">Fulfilled</div>
                        </div>

                        <div class="rounded-xl bg-orange-50 px-2 py-3 text-center">
                            <div class="text-lg font-bold text-orange-800">
                                {{ formatQuantity($requirement->total_pending_quantity) }}
                            </div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-orange-700">Pending</div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Primary Material</div>
                        <div class="mt-1 font-semibold text-gray-800">{{ $primaryMaterial }}</div>

                        @if($primaryVariant)
                            <div class="mt-1 text-xs text-gray-500">{{ $primaryVariant }}</div>
                        @endif

                        <div class="mt-2 text-sm font-bold text-[#0F2A52]">
                            Required {{ formatQuantity($requirement->total_required_quantity) }}
                        </div>

                        @if($hasNewItems && $requirement->items->count() > 1)
                            <div class="mt-1 text-xs text-gray-500">
                                +{{ $requirement->items->count() - 1 }} more material item{{ $requirement->items->count() - 1 === 1 ? '' : 's' }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ $requirement->status }}
                        </span>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClasses }}">
                            {{ $requirement->priority }} Priority
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('material-requirements.show', $requirement) }}"
                           class="rounded-xl bg-[#0F2A52] px-4 py-3 text-center text-sm font-semibold text-white">
                            View
                        </a>

                        @if($requirement->status === 'Draft' && $canEdit)
                            <a href="{{ route('material-requirements.edit', $requirement) }}"
                               class="rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-semibold text-white">
                                Edit
                            </a>
                        @else
                            <div class="flex items-center justify-center rounded-xl bg-gray-100 px-4 py-3 text-center text-xs font-semibold text-gray-500">
                                {{ $requirement->status }}
                            </div>
                        @endif
                    </div>

                    @if($requirement->status === 'Draft')
                        <form method="POST"
                              action="{{ route('material-requirements.submit', $requirement) }}"
                              class="mt-3">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm('Submit this material requirement for approval?')"
                                    class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">
                                Submit Requirement
                            </button>
                        </form>
                    @endif

                    @if($requirement->status === 'Submitted' && $canApprove)
                        <form method="POST"
                              action="{{ route('material-requirements.approve', $requirement) }}"
                              class="mt-3">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    onclick="return confirm('Approve this material requirement?')"
                                    class="w-full rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white">
                                Approve Requirement
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center shadow-sm">
                <div class="text-base font-semibold text-gray-700">No material requirements found</div>
                <p class="mt-2 text-sm text-gray-500">Adjust the filters or create the first requirement.</p>

                @if($canCreate)
                    <a href="{{ route('material-requirements.create') }}"
                       class="mt-4 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">
                        + Add Requirement
                    </a>
                @endif
            </div>
        @endforelse

        @if($requirements->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $requirements->links() }}
            </div>
        @endif
    </div>

<div class="hidden lg:block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-[1450px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-3 py-3 text-center">#</th>
                        <th class="px-3 py-3 text-left">Required Date</th>
                        <th class="px-3 py-3 text-left">Project</th>
                        <th class="px-3 py-3 text-left">Priority</th>
                        <th class="px-3 py-3 text-left">Items</th>
                        <th class="px-3 py-3 text-right">Required Qty</th>
                        <th class="px-3 py-3 text-right">Fulfilled Qty</th>
                        <th class="px-3 py-3 text-right">Pending Qty</th>
                        <th class="px-3 py-3 text-left">Status</th>
                        <th class="px-3 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($requirements as $index => $requirement)

                        @php
                            $hasNewItems = $requirement->items->isNotEmpty();

                            $priorityClasses = match($requirement->priority) {
                                'Urgent' => 'bg-red-100 text-red-800',
                                'High' => 'bg-orange-100 text-orange-800',
                                'Normal' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800',
                            };

                            $statusClasses = match($requirement->status) {
                                'Approved' => 'bg-green-100 text-green-800',
                                'Submitted' => 'bg-blue-100 text-blue-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-3 py-3 text-center">
                                {{ $requirements->firstItem() + $index }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                {{ $requirement->required_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-semibold text-gray-800">
                                    {{ $requirement->project?->project_name ?? '-' }}
                                </div>

                                @if($requirement->block)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $requirement->block->name }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClasses }}">
                                    {{ $requirement->priority }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($requirement->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                                </div>

                                                <div class="text-xs text-gray-500">
                                                    {{ collect([
                                                        $item->brand?->brand_name,
                                                        $item->specification?->specification_name,
                                                        $item->grade?->grade_name,
                                                    ])->filter()->implode(' • ') ?: '-' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="font-semibold text-gray-800">
                                        {{ $requirement->material?->material_name ?? '-' }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        Legacy material record
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-right font-semibold text-blue-700">
                                {{ formatQuantity($requirement->total_required_quantity) }}
                            </td>

                            <td class="px-3 py-3 text-right font-semibold text-green-700">
                                {{ formatQuantity($requirement->total_fulfilled_quantity) }}
                            </td>

                            <td class="px-3 py-3 text-right font-semibold text-orange-700">
                                {{ formatQuantity($requirement->total_pending_quantity) }}
                            </td>

                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $requirement->status }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex flex-col gap-2">

                                    <a href="{{ route('material-requirements.show', $requirement) }}"
                                       class="rounded bg-slate-700 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-slate-800">
                                        View
                                    </a>

                                    @if(
                                        $requirement->status === 'Draft'
                                        && $canEdit
                                    )
                                        <a href="{{ route('material-requirements.edit', $requirement) }}"
                                           class="rounded bg-yellow-500 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-yellow-600">
                                            Edit
                                        </a>
                                    @endif

                                    @if($requirement->status === 'Draft')
                                        <form method="POST"
                                              action="{{ route('material-requirements.submit', $requirement) }}">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    onclick="return confirm('Submit this material requirement for approval?')"
                                                    class="w-full rounded bg-blue-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-blue-700">
                                                Submit
                                            </button>
                                        </form>
                                    @endif

                                    @if(
                                        $requirement->status === 'Submitted'
                                        && $canApprove
                                    )
                                        <form method="POST"
                                              action="{{ route('material-requirements.approve', $requirement) }}">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    onclick="return confirm('Approve this material requirement?')"
                                                    class="w-full rounded bg-green-600 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-green-700">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10"
                                class="px-6 py-12 text-center text-gray-500">
                                No material requirements found for the selected filters.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        @if($requirements->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $requirements->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
