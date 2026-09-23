
@extends('layouts.app')

@section('content')

@php
    $canManage = auth()->user()?->hasPermission('materials.manage') ?? false;

    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
@endphp

{{-- Page heading --}}
<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Brand Segments
        </h1>

        <p class="mt-1 text-gray-500">
            Manage reusable Brand classifications. Product associations are maintained separately in the Brand Master.
        </p>
    </div>

    <div class="flex flex-wrap gap-2">

        <a href="{{ route('brand-masters.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            Material Brands
        </a>

        @if($canManage)
            <a href="{{ route('brand-segments.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                + Add Brand Segment
            </a>
        @endif

    </div>

</div>

{{-- Success and error messages --}}
@if(session('success'))
    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

{{-- Filters --}}
<div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

    <form method="GET"
          action="{{ route('brand-segments.index') }}"
          class="grid grid-cols-1 items-end gap-4 md:grid-cols-12">

        <div class="md:col-span-7">

            <label for="search"
                   class="mb-1 block text-sm font-semibold text-gray-700">
                Search
            </label>

            <input id="search"
                   type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Segment code, name or remarks"
                   class="{{ $inputClass }}">

        </div>

        <div class="md:col-span-3">

            <label for="status"
                   class="mb-1 block text-sm font-semibold text-gray-700">
                Status
            </label>

            <select id="status"
                    name="status"
                    class="{{ $inputClass }}">

                <option value="">All Statuses</option>

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

        <div class="flex gap-2 md:col-span-2">

            <button type="submit"
                    class="flex-1 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Filter
            </button>

            <a href="{{ route('brand-segments.index') }}"
               class="rounded-lg bg-gray-500 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                Clear
            </a>

        </div>

    </form>

</div>

{{-- Brand Segment register --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-4 py-3">

        <h2 class="text-sm font-semibold text-gray-800">
            Segment Register
        </h2>

        <span class="text-xs text-gray-500">
            {{ number_format($brandSegments->total()) }} record(s)
        </span>

    </div>

    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">

                <tr>

                    <th class="whitespace-nowrap px-4 py-3 text-left">
                        #
                    </th>

                    <th class="whitespace-nowrap px-4 py-3 text-left">
                        Code
                    </th>

                    <th class="px-4 py-3 text-left">
                        Brand Segment
                    </th>

                    <th class="whitespace-nowrap px-4 py-3 text-center">
                        Brands
                    </th>

                    <th class="whitespace-nowrap px-4 py-3 text-center">
                        Sort Order
                    </th>

                    <th class="whitespace-nowrap px-4 py-3 text-center">
                        Status
                    </th>

                    <th class="px-4 py-3 text-left">
                        Remarks
                    </th>

                    @if($canManage)
                        <th class="whitespace-nowrap px-4 py-3 text-center">
                            Actions
                        </th>
                    @endif

                </tr>

            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($brandSegments as $segment)

                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                            {{ $brandSegments->firstItem() + $loop->index }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700">
                            {{ $segment->segment_code }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-gray-800">
                            {{ $segment->segment_name }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center text-gray-700">
                            {{ number_format($segment->brands_count) }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center text-gray-600">
                            {{ $segment->sort_order }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">

                            @if($segment->is_active)

                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                    Active
                                </span>

                            @else

                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td class="max-w-xs px-4 py-3 text-gray-600">

                            @if($segment->remarks)

                                <div class="max-w-xs whitespace-normal">
                                    {{ $segment->remarks }}
                                </div>

                            @else

                                <span class="text-gray-400">—</span>

                            @endif

                        </td>

                        @if($canManage)

                            <td class="whitespace-nowrap px-4 py-3">

                                <div class="flex items-center justify-center gap-2">

                                    <a href="{{ route('brand-segments.edit', $segment) }}"
                                       class="rounded bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('brand-segments.toggle-status', $segment) }}"
                                          class="inline"
                                          onsubmit="return confirm(@js($segment->is_active ? 'Deactivate this Brand Segment? Existing Brands will remain unchanged.' : 'Activate this Brand Segment?'))">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="rounded px-3 py-1.5 text-xs font-semibold text-white {{ $segment->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">

                                            {{ $segment->is_active ? 'Deactivate' : 'Activate' }}

                                        </button>

                                    </form>

                                </div>

                            </td>

                        @endif

                    </tr>

                @empty

                    <tr>
                        <td colspan="{{ $canManage ? 8 : 7 }}"
                            class="px-6 py-10 text-center text-gray-500">
                            No Brand Segments found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($brandSegments->hasPages())

        <div class="border-t border-gray-200 px-4 py-4">
            {{ $brandSegments->links() }}
        </div>

    @endif

</div>

@endsection