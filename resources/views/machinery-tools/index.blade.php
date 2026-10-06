@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Machinery & Equipment Master
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Define reusable equipment types used across Ravion projects.
            </p>
        </div>

        <a
            href="{{ route('machinery-tools.create') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-[#0F2A52] text-white text-sm font-semibold hover:opacity-90"
        >
            + Add Equipment Type
        </a>

    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('machinery-tools.index') }}"
        class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5"
    >

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search equipment, code or category..."
                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >

            <select
                name="category"
                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">All Categories</option>

                @foreach($categories as $category)
                    <option
                        value="{{ $category }}"
                        @selected(request('category') === $category)
                    >
                        {{ $category }}
                    </option>
                @endforeach
            </select>

            <select
                name="status"
                class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>
                    Active
                </option>
                <option value="inactive" @selected(request('status') === 'inactive')>
                    Inactive
                </option>
            </select>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="flex-1 rounded-lg bg-gray-900 text-white px-4 py-2 text-sm font-semibold"
                >
                    Filter
                </button>

                <a
                    href="{{ route('machinery-tools.index') }}"
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
                            Category
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Tracking
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Meter
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            Fuel / Power
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

                    @forelse($machineries as $machinery)

                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 text-sm font-mono text-gray-600 whitespace-nowrap">
                                {{ $machinery->code }}
                            </td>

                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('machinery-tools.show', $machinery) }}"
                                    class="text-sm font-semibold text-[#0F2A52] hover:underline"
                                >
                                    {{ $machinery->machine_name }}
                                </a>

                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $machinery->unit }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ $machinery->category }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ $machinery->tracking_mode === 'individual'
                                    ? 'Individual'
                                    : 'Pooled' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ ucwords(str_replace('_', ' ', $machinery->meter_type)) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ ucfirst($machinery->fuel_type) }}
                            </td>

                            <td class="px-4 py-3 text-center">

                                @if($machinery->is_active)
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                        Inactive
                                    </span>
                                @endif

                            </td>

                            <td class="px-4 py-3">

                                <div class="flex justify-end gap-2">

                                    <a
                                        href="{{ route('machinery-tools.show', $machinery) }}"
                                        class="text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route('machinery-tools.edit', $machinery) }}"
                                        class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                    >
                                        Edit
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">

                                <div class="text-base font-semibold text-gray-700">
                                    No machinery or equipment types found.
                                </div>

                                <div class="text-sm text-gray-400 mt-1">
                                    Add an equipment type or change the current filters.
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($machineries->hasPages())
            <div class="border-t border-gray-200 px-4 py-4">
                {{ $machineries->links() }}
            </div>
        @endif

    </div>

</div>

@endsection