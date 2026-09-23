@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
@endphp

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Material Brands
        </h1>

        <p class="mt-1 text-gray-500">
            Manage reusable Brand identities and their commercial Brand Segments.
        </p>
    </div>

    <a href="{{ route('brand-masters.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700">
        + Add Brand
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

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

    <form method="GET"
          action="{{ route('brand-masters.index') }}"
          class="grid grid-cols-1 gap-4 md:grid-cols-4">

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">
                Search
            </label>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Brand, code or segment"
                   class="{{ $inputClass }}">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">
                Brand Segment
            </label>

            <select name="brand_segment_id"
                    class="{{ $inputClass }}">

                <option value="">
                    All Brand Segments
                </option>

                @foreach($brandSegments as $segment)
                    <option value="{{ $segment->id }}"
                        {{ (string) request('brand_segment_id') === (string) $segment->id ? 'selected' : '' }}>
                        {{ $segment->segment_name }}
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

            <a href="{{ route('brand-masters.index') }}"
               class="rounded-lg bg-gray-500 px-4 py-2 font-semibold text-white hover:bg-gray-600">
                Clear
            </a>

        </div>

    </form>

</div>

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">

                <tr>
                    <th class="px-4 py-3 text-left">
                        #
                    </th>

                    <th class="px-4 py-3 text-left">
                        Brand Segment
                    </th>

                    <th class="px-4 py-3 text-left">
                        Brand
                    </th>

                    <th class="px-4 py-3 text-left">
                        Brand Code
                    </th>

                    <th class="px-4 py-3 text-center">
                        Sequence
                    </th>

                    <th class="px-4 py-3 text-center">
                        Status
                    </th>

                    <th class="px-4 py-3 text-left">
                        Remarks
                    </th>

                    <th class="px-4 py-3 text-center">
                        Actions
                    </th>
                </tr>

            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($brands as $index => $brand)

                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3 text-gray-600">
                            {{ $brands->firstItem() + $index }}
                        </td>

                        <td class="px-4 py-3">

                            @if($brand->segment)

                                <div class="font-semibold text-gray-800">
                                    {{ $brand->segment->segment_name }}
                                </div>

                                <div class="mt-0.5 text-xs text-gray-500">
                                    {{ $brand->segment->segment_code }}
                                </div>

                            @else

                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                    Legacy / Unclassified
                                </span>

                            @endif

                        </td>

                        <td class="px-4 py-3">

                            <span class="font-semibold text-gray-800">
                                {{ $brand->brand_name }}
                            </span>

                            @if(!$brand->segment && $brand->materialType)

                                <div class="mt-1 text-xs text-gray-500">
                                    Legacy Product:
                                    {{ $brand->materialType->material_type_name }}
                                </div>

                            @endif

                        </td>

                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $brand->brand_code ?: '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $brand->sequence }}
                        </td>

                        <td class="px-4 py-3 text-center">

                            @if($brand->is_active)

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

                            @if($brand->remarks)

                                <div class="max-w-md whitespace-normal text-gray-700">
                                    {{ $brand->remarks }}
                                </div>

                            @else

                                <span class="text-gray-400">
                                    -
                                </span>

                            @endif

                        </td>

                        <td class="px-4 py-3">

                            <div class="flex flex-wrap justify-center gap-2">

                                <a href="{{ route('brand-masters.edit', $brand) }}"
                                   class="rounded bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('brand-masters.toggle-status', $brand) }}"
                                      class="inline">

                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            onclick="return confirm('Change this brand status?')"
                                            class="rounded px-3 py-1.5 text-xs font-semibold text-white
                                            {{ $brand->is_active
                                                ? 'bg-red-600 hover:bg-red-700'
                                                : 'bg-green-600 hover:bg-green-700' }}">

                                        {{ $brand->is_active
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
                            No material brands found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($brands->hasPages())
        <div class="border-t border-gray-200 px-4 py-4">
            {{ $brands->links() }}
        </div>
    @endif

</div>

@endsection