@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Equipment Allocations & Transfers
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Track equipment movement between projects and Company Yard.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('machinery-equipment.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Equipment Register
            </a>

            <a href="{{ route('machinery-equipment-allocations.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                + New Movement
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET"
              action="{{ route('machinery-equipment-allocations.index') }}"
              class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Search
                </label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Movement no., equipment, challan, vehicle..."
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Equipment
                </label>
                <select name="equipment_id"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">All Equipment</option>

                    @foreach ($equipment as $item)
                        <option value="{{ $item->id }}"
                            @selected((string) request('equipment_id') === (string) $item->id)>
                            {{ $item->equipment_code }}
                            — {{ $item->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Project
                </label>
                <select name="project_id"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">All Projects</option>

                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"
                            @selected((string) request('project_id') === (string) $project->id)>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Movement Type
                </label>
                <select name="movement_type"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">All Types</option>

                    @foreach ($movementTypes as $value => $label)
                        <option value="{{ $value }}"
                            @selected(request('movement_type') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Status
                </label>
                <select name="status"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">All Statuses</option>

                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}"
                            @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    From Date
                </label>
                <input type="date"
                       name="date_from"
                       value="{{ request('date_from') }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    To Date
                </label>
                <input type="date"
                       name="date_to"
                       value="{{ request('date_to') }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div class="flex items-end gap-2 xl:col-span-4">
                <button type="submit"
                        class="rounded-lg bg-gray-900 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Apply Filters
                </button>

                <a href="{{ route('machinery-equipment-allocations.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            Movement
                        </th>

                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            Date
                        </th>

                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            Equipment
                        </th>

                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            Type
                        </th>

                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            From
                        </th>

                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            To
                        </th>

                        <th class="px-4 py-3 text-right font-semibold text-gray-700">
                            Qty
                        </th>

                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right font-semibold text-gray-700">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($allocations as $allocation)
                        @php
                            $statusClasses = match ($allocation->status) {
                                'received' =>
                                    'bg-green-100 text-green-800',

                                'pending' =>
                                    'bg-yellow-100 text-yellow-800',

                                'in_transit' =>
                                    'bg-blue-100 text-blue-800',

                                'cancelled' =>
                                    'bg-red-100 text-red-800',

                                default =>
                                    'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route(
                                    'machinery-equipment-allocations.show',
                                    $allocation
                                ) }}"
                                   class="font-semibold text-gray-900 hover:underline">
                                    {{ $allocation->allocation_number }}
                                </a>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                {{ $allocation->movement_date?->format('d M Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">
                                    {{ $allocation->equipment?->equipment_code }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    {{ $allocation->equipment?->display_name }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                {{ $movementTypes[$allocation->movement_type]
                                    ?? ucwords(str_replace('_', ' ', $allocation->movement_type)) }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $allocation->fromProject?->project_name
                                    ?? 'Company Yard' }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $allocation->toProject?->project_name
                                    ?? 'Company Yard' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-900">
                                {{ rtrim(rtrim(
                                    number_format((float) $allocation->quantity, 3, '.', ''),
                                    '0'
                                ), '.') }}
                                {{ $allocation->unit }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $statuses[$allocation->status]
                                        ?? ucwords(str_replace('_', ' ', $allocation->status)) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route(
                                    'machinery-equipment-allocations.show',
                                    $allocation
                                ) }}"
                                   class="font-medium text-gray-700 hover:text-gray-900 hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"
                                class="px-6 py-12 text-center text-gray-500">
                                No equipment movements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($allocations as $allocation)
            @php
                $statusClasses = match ($allocation->status) {
                    'received' =>
                        'bg-green-100 text-green-800',

                    'pending' =>
                        'bg-yellow-100 text-yellow-800',

                    'in_transit' =>
                        'bg-blue-100 text-blue-800',

                    'cancelled' =>
                        'bg-red-100 text-red-800',

                    default =>
                        'bg-gray-100 text-gray-700',
                };
            @endphp

            <a href="{{ route(
                'machinery-equipment-allocations.show',
                $allocation
            ) }}"
               class="block rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-bold text-gray-900">
                            {{ $allocation->equipment?->equipment_code }}
                        </div>

                        <div class="mt-0.5 text-sm text-gray-600">
                            {{ $allocation->equipment?->display_name }}
                        </div>
                    </div>

                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                        {{ $statuses[$allocation->status]
                            ?? ucwords(str_replace('_', ' ', $allocation->status)) }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">
                            Movement
                        </div>
                        <div class="font-medium text-gray-800">
                            {{ $allocation->allocation_number }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            Quantity
                        </div>
                        <div class="font-medium text-gray-800">
                            {{ rtrim(rtrim(
                                number_format((float) $allocation->quantity, 3, '.', ''),
                                '0'
                            ), '.') }}
                            {{ $allocation->unit }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            From
                        </div>
                        <div class="font-medium text-gray-800">
                            {{ $allocation->fromProject?->project_name
                                ?? 'Company Yard' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">
                            To
                        </div>
                        <div class="font-medium text-gray-800">
                            {{ $allocation->toProject?->project_name
                                ?? 'Company Yard' }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 border-t border-gray-100 pt-3 text-xs text-gray-500">
                    {{ $allocation->movement_date?->format('d M Y') }}
                    •
                    {{ $movementTypes[$allocation->movement_type]
                        ?? ucwords(str_replace('_', ' ', $allocation->movement_type)) }}
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center text-gray-500">
                No equipment movements found.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($allocations->hasPages())
        <div class="mt-6">
            {{ $allocations->links() }}
        </div>
    @endif
</div>
@endsection