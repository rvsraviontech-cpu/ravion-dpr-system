@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

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
            <h1 class="text-3xl font-bold text-gray-800">
                Material Requirements
            </h1>

            <p class="mt-1 text-gray-500">
                Manage project-wise material requirements, fulfilment and approval workflow.
            </p>
        </div>

        @if($canCreate)
            <a href="{{ route('material-requirements.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white shadow-sm hover:bg-blue-700">
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

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Draft Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-yellow-700">
                {{ $draftCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Submitted Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-orange-700">
                {{ $submittedCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Approved Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-green-700">
                {{ $approvedCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Urgent Requirements
            </p>

            <p class="mt-2 text-2xl font-bold text-red-700">
                {{ $urgentCount }}
            </p>
        </div>

    </div>

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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

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
