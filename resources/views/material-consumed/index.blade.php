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

    $canCreate = $hasPermission('material_consumed.create');
    $canEdit = $hasPermission('material_consumed.edit');
    $canApprove = $hasPermission('material_consumed.approve')
        || in_array($roleName, ['PMO', 'DGM'], true);
@endphp

<div class="mx-auto max-w-full">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Material Consumed
            </h1>

            <p class="mt-1 text-gray-500">
                Track material consumption, wastage and approval status by project and activity.
            </p>
        </div>

        @if($canCreate)
            <a href="{{ route('material-consumed.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white shadow-sm hover:bg-blue-700">
                + Add Material Consumed
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

    {{-- Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Qty Consumed Today
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-700">
                {{ formatQuantity($totalConsumedToday) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Wastage Today
            </p>

            <p class="mt-2 text-2xl font-bold text-red-700">
                {{ formatQuantity($totalWastageToday) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Draft Entries
            </p>

            <p class="mt-2 text-2xl font-bold text-yellow-700">
                {{ $draftCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Submitted Entries
            </p>

            <p class="mt-2 text-2xl font-bold text-orange-700">
                {{ $submittedCount }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">
                Approved Entries
            </p>

            <p class="mt-2 text-2xl font-bold text-green-700">
                {{ $approvedCount }}
            </p>
        </div>

    </div>

    {{-- Filters --}}
    <form method="GET"
          action="{{ route('material-consumed.index') }}"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Date
                </label>

                <input type="date"
                       name="consumed_date"
                       value="{{ request('consumed_date') }}"
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
                       placeholder="Material, brand, activity, contractor">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('material-consumed.index') }}"
                   class="rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Clear
                </a>
            </div>

        </div>

    </form>

    {{-- Consumption Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-[1450px] w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-3 py-3 text-center">#</th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">Project</th>
                        <th class="px-3 py-3 text-left">Activity</th>
                        <th class="px-3 py-3 text-left">Material</th>
                        <th class="px-3 py-3 text-left">Specification</th>
                        <th class="px-3 py-3 text-left">Brand</th>
                        <th class="px-3 py-3 text-right">Consumed</th>
                        <th class="px-3 py-3 text-right">Wastage</th>
                        <th class="px-3 py-3 text-left">Unit</th>
                        <th class="px-3 py-3 text-left">Contractor</th>
                        <th class="px-3 py-3 text-left">Status</th>
                        <th class="px-3 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($materialConsumeds as $index => $materialConsumed)

                        @php
                            $hasNewItems = $materialConsumed->items->isNotEmpty();

                            $statusClasses = match($materialConsumed->status) {
                                'Approved' => 'bg-green-100 text-green-800',
                                'Submitted' => 'bg-blue-100 text-blue-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-3 py-3 text-center">
                                {{ $materialConsumeds->firstItem() + $index }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                {{ $materialConsumed->consumed_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-semibold text-gray-800">
                                    {{ $materialConsumed->project?->project_name ?? '-' }}
                                </div>

                                @if($materialConsumed->block)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $materialConsumed->block->name }}
                                    </div>
                                @endif
                            </td>

                            {{-- Activity --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->activity?->activity_name ?? '-' }}
                                                </div>

                                                @if($item->activityDivision)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $item->activityDivision->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="font-semibold text-gray-800">
                                        {{ $materialConsumed->activity?->activity_name ?? '-' }}
                                    </div>

                                    @if($materialConsumed->activityDivision)
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $materialConsumed->activityDivision->name }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- Material --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->materialType?->material_type_name ?? '-' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $materialConsumed->material?->material_name ?? '-' }}
                                @endif
                            </td>

                            {{-- Specification --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ $item->specification?->specification_name ?? '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Brand --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ $item->brand?->brand_name ?? '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Consumed --}}
                            <td class="px-3 py-3 text-right">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1 font-semibold">
                                                {{ formatQuantity($item->quantity_consumed) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ formatQuantity($materialConsumed->quantity_consumed) }}
                                @endif
                            </td>

                            {{-- Wastage --}}
                            <td class="px-3 py-3 text-right">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ formatQuantity($item->wastage_quantity) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ formatQuantity($materialConsumed->wastage_quantity) }}
                                @endif
                            </td>

                            {{-- Unit --}}
                            <td class="px-3 py-3">
                                @if($hasNewItems)
                                    <div class="space-y-2">
                                        @foreach($materialConsumed->items as $item)
                                            <div class="min-h-[42px] py-1">
                                                {{ $item->unit?->unit_name ?? '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $materialConsumed->unit ?? '-' }}
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                {{ $materialConsumed->contractor?->contractor_name ?? '-' }}
                            </td>

                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $materialConsumed->status }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex flex-col gap-2">

                                    <a href="{{ route('material-consumed.show', $materialConsumed) }}"
                                       class="rounded bg-slate-700 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-slate-800">
                                        View
                                    </a>

                                    @if(
                                        $materialConsumed->status === 'Draft'
                                        && $canEdit
                                    )
                                        <a href="{{ route('material-consumed.edit', $materialConsumed) }}"
                                           class="rounded bg-yellow-500 px-3 py-1.5 text-center text-xs font-semibold text-white hover:bg-yellow-600">
                                            Edit
                                        </a>
                                    @endif

                                    @if(
                                        $materialConsumed->status === 'Submitted'
                                        && $canApprove
                                    )
                                        <form method="POST"
                                              action="{{ route('material-consumed.approve', $materialConsumed) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    onclick="return confirm('Approve this material consumption entry?')"
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
                            <td colspan="13"
                                class="px-6 py-12 text-center text-gray-500">
                                No material consumption entries found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        @if($materialConsumeds->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $materialConsumeds->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
