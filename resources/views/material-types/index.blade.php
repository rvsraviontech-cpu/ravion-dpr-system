@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
@endphp

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Types
        </h1>

        <p class="mt-1 text-gray-500">
            Manage reusable construction material types and their default units.
        </p>
    </div>

    <a href="{{ route('material-types.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700">
        + Add Material Type
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
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

    <form method="GET"
          action="{{ route('material-types.index') }}"
          class="grid grid-cols-1 gap-4 md:grid-cols-5">

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">
                Search
            </label>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Material Type or Group"
                   class="{{ $inputClass }}">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">
                Material Group
            </label>

            <select name="material_group"
                    class="{{ $inputClass }}">

                <option value="">
                    All Material Groups
                </option>

                @foreach($materialGroups as $group)
                    <option value="{{ $group }}"
                        {{ request('material_group') === $group ? 'selected' : '' }}>
                        {{ $group }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">
                Default Unit
            </label>

            <select name="unit_master_id"
                    class="{{ $inputClass }}">

                <option value="">
                    All Units
                </option>

                @foreach($units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ (string) request('unit_master_id') === (string) $unit->id ? 'selected' : '' }}>
                        {{ $unit->unit_name }}
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

                <option value="">
                    All Statuses
                </option>

                <option value="1"
                    {{ request('status') === '1' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="0"
                    {{ request('status') === '0' ? 'selected' : '' }}>
                    Inactive
                </option>

            </select>
        </div>

        <div class="flex items-end gap-2">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                Filter
            </button>

            <a href="{{ route('material-types.index') }}"
               class="rounded-lg bg-gray-500 px-4 py-2 font-semibold text-white hover:bg-gray-600">
                Clear
            </a>

        </div>

    </form>

</div>

{{-- Summary --}}
<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">
            Displayed Records
        </p>

        <p class="mt-2 text-3xl font-bold text-gray-800">
            {{ $materialTypes->total() }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">
            Material Groups
        </p>

        <p class="mt-2 text-3xl font-bold text-blue-700">
            {{ $materialGroups->count() }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">
            Units Assigned on Page
        </p>

        <p class="mt-2 text-3xl font-bold text-green-700">
            {{ $materialTypes->whereNotNull('unit_master_id')->count() }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">
            Units Missing on Page
        </p>

        <p class="mt-2 text-3xl font-bold text-red-700">
            {{ $materialTypes->whereNull('unit_master_id')->count() }}
        </p>
    </div>

</div>

{{-- Table --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">

                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Material Group</th>
                    <th class="px-4 py-3 text-left">Material Type</th>
                    <th class="px-4 py-3 text-center">Default Unit</th>
                    <th class="px-4 py-3 text-center">Sequence</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Remarks</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>

            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($materialTypes as $index => $type)

                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3">
                            {{ $materialTypes->firstItem() + $index }}
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $type->material_group ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">
                                {{ $type->material_type_name }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">

                            @if($type->unit)
                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                    {{ $type->unit->unit_name }}
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                    Unit Missing
                                </span>
                            @endif

                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $type->sequence }}
                        </td>

                        <td class="px-4 py-3 text-center">

                            @if($type->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                    Inactive
                                </span>
                            @endif

                        </td>

                        <td class="px-4 py-3">
                            {{ $type->remarks ?? '-' }}
                        </td>

                        <td class="px-4 py-3">

                            <div class="flex flex-wrap justify-center gap-2">

                                <a href="{{ route('material-types.edit', $type) }}"
                                   class="rounded bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('material-types.destroy', $type) }}"
                                      class="inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Change this Material Type status?')"
                                            class="rounded px-3 py-1.5 text-xs font-semibold text-white
                                            {{ $type->is_active
                                                ? 'bg-red-600 hover:bg-red-700'
                                                : 'bg-green-600 hover:bg-green-700' }}">

                                        {{ $type->is_active
                                            ? 'Deactivate'
                                            : 'Activate' }}

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8"
                            class="px-6 py-10 text-center text-gray-500">
                            No Material Types found for the selected filters.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($materialTypes->hasPages())
        <div class="border-t border-gray-200 px-4 py-4">
            {{ $materialTypes->links() }}
        </div>
    @endif

</div>

@endsection