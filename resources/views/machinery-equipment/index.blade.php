@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Machinery & Equipment Register
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Physical equipment, pooled tools, ownership and current site status.
            </p>
        </div>

        <a
            href="{{ route('machinery-equipment.create') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-[#0F2A52] text-white text-sm font-semibold hover:opacity-90"
        >
            + Register Equipment
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('machinery-equipment.index') }}"
        class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">

            <div class="xl:col-span-2">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Code, equipment, make, model, registration..."
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <select
                name="ownership_type"
                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">All Ownership</option>

                @foreach($ownershipTypes as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(request('ownership_type') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select
                name="status"
                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">All Statuses</option>

                @foreach($statuses as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(request('status') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select
                name="project_id"
                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">All Projects</option>

                @foreach($projects as $project)
                    <option
                        value="{{ $project->id }}"
                        @selected((string) request('project_id') === (string) $project->id)
                    >
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="flex-1 rounded-lg bg-gray-900 text-white px-4 py-2 text-sm font-semibold"
                >
                    Filter
                </button>

                <a
                    href="{{ route('machinery-equipment.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600"
                >
                    Clear
                </a>
            </div>

        </div>
    </form>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Code
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Equipment
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Ownership
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Project
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Tracking
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-gray-500">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">

                    @forelse($equipment as $item)

                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-mono font-semibold text-gray-700">
                                    {{ $item->equipment_code }}
                                </div>

                                @if($item->asset_number)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        Asset: {{ $item->asset_number }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('machinery-equipment.show', $item) }}"
                                    class="text-sm font-semibold text-[#0F2A52] hover:underline"
                                >
                                    {{ $item->display_name }}
                                </a>

                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $item->machineryTool?->category ?? '—' }}
                                </div>

                                @if($item->make || $item->model)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ trim(($item->make ?? '') . ' ' . ($item->model ?? '')) }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600">
                                <div class="font-medium text-gray-700">
                                    {{ $item->ownership_label }}
                                </div>

                                @if($item->isRented() && $item->vendor)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $item->vendor->vendor_name }}
                                    </div>
                                @elseif($item->isContractorProvided() && $item->contractor)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $item->contractor->contractor_name }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ $item->currentProject?->project_name ?? 'Not Allocated' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                @if($item->tracking_mode === 'pooled')
                                    <div class="font-medium">
                                        Pooled
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        {{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}
                                        {{ $item->unit }}
                                    </div>
                                @else
                                    Individual
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span
                                    @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-green-100 text-green-700' => in_array($item->status, ['available', 'working']),
                                        'bg-blue-100 text-blue-700' => $item->status === 'allocated',
                                        'bg-yellow-100 text-yellow-700' => in_array($item->status, ['idle', 'maintenance']),
                                        'bg-red-100 text-red-700' => $item->status === 'breakdown',
                                        'bg-gray-100 text-gray-600' => in_array($item->status, ['returned', 'inactive']),
                                    ])
                                >
                                    {{ $item->status_label }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('machinery-equipment.show', $item) }}"
                                        class="text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route('machinery-equipment.edit', $item) }}"
                                        class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                    >
                                        Edit
                                    </a>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">

                                <div class="text-base font-semibold text-gray-700">
                                    No machinery or equipment registered.
                                </div>

                                <div class="text-sm text-gray-400 mt-1">
                                    Register company-owned, rented or contractor-provided equipment.
                                </div>

                                <a
                                    href="{{ route('machinery-equipment.create') }}"
                                    class="mt-4 inline-flex px-4 py-2.5 rounded-lg bg-[#0F2A52] text-white text-sm font-semibold hover:opacity-90"
                                >
                                    Register First Equipment
                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($equipment->hasPages())
            <div class="border-t border-gray-200 px-4 py-4">
                {{ $equipment->links() }}
            </div>
        @endif

    </div>

</div>

@endsection