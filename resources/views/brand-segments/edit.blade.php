
@extends('layouts.app')

@section('content')

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Edit Brand Segment
        </h1>

        <p class="mt-1 text-gray-500">
            Update the Brand Segment classification and its display settings.
        </p>
    </div>

    <a href="{{ route('brand-segments.index') }}"
       class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
        Back to Brand Segments
    </a>

</div>

@if($errors->any())

    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">

        <p class="font-semibold">
            Please correct the following:
        </p>

        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>

@endif

<div class="max-w-4xl overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="border-b border-gray-200 px-6 py-4">

        <h2 class="text-lg font-semibold text-gray-800">
            Brand Segment Details
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            {{ $brandSegment->segment_name }}
            · {{ $brandSegment->segment_code }}
        </p>

    </div>

    <form method="POST"
          action="{{ route('brand-segments.update', $brandSegment) }}">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

            {{-- Segment Code --}}
            <div>

                <label for="segment_code"
                       class="mb-1 block text-sm font-semibold text-gray-700">
                    Segment Code <span class="text-red-600">*</span>
                </label>

                <input id="segment_code"
                       name="segment_code"
                       type="text"
                       maxlength="20"
                       required
                       value="{{ old('segment_code', $brandSegment->segment_code) }}"
                       class="w-full rounded-lg border-gray-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-1 text-xs text-gray-500">
                    Unique short identifier for this Brand Segment.
                </p>

                @error('segment_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Segment Name --}}
            <div>

                <label for="segment_name"
                       class="mb-1 block text-sm font-semibold text-gray-700">
                    Segment Name <span class="text-red-600">*</span>
                </label>

                <input id="segment_name"
                       name="segment_name"
                       type="text"
                       maxlength="150"
                       required
                       value="{{ old('segment_name', $brandSegment->segment_name) }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-1 text-xs text-gray-500">
                    This name appears in Brand Master dropdowns and registers.
                </p>

                @error('segment_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Sort Order --}}
            <div>

                <label for="sort_order"
                       class="mb-1 block text-sm font-semibold text-gray-700">
                    Sort Order
                </label>

                <input id="sort_order"
                       name="sort_order"
                       type="number"
                       min="0"
                       step="1"
                       value="{{ old('sort_order', $brandSegment->sort_order) }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-1 text-xs text-gray-500">
                    Lower numbers appear first in Brand Segment dropdowns.
                </p>

                @error('sort_order')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Status --}}
            <div>

                <label for="is_active"
                       class="mb-1 block text-sm font-semibold text-gray-700">
                    Status
                </label>

                <select id="is_active"
                        name="is_active"
                        class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                    <option value="1"
                        @selected((string) old('is_active', (int) $brandSegment->is_active) === '1')>
                        Active
                    </option>

                    <option value="0"
                        @selected((string) old('is_active', (int) $brandSegment->is_active) === '0')>
                        Inactive
                    </option>

                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Deactivation does not deactivate existing Brands or their Product associations.
                </p>

                @error('is_active')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Remarks --}}
            <div class="md:col-span-2">

                <label for="remarks"
                       class="mb-1 block text-sm font-semibold text-gray-700">
                    Remarks
                </label>

                <textarea id="remarks"
                          name="remarks"
                          rows="3"
                          maxlength="2000"
                          placeholder="Describe the types of Brands covered by this segment."
                          class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('remarks', $brandSegment->remarks) }}</textarea>

                @error('remarks')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

        </div>

        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">

            <div class="flex flex-wrap items-center justify-between gap-3">

                <span class="text-xs text-gray-500">
                    Associated Brands:
                    <span class="font-semibold text-gray-700">
                        {{ number_format($brandSegment->brands_count) }}
                    </span>
                </span>

                <div class="flex flex-wrap gap-3">

                    <a href="{{ route('brand-segments.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        Cancel
                    </a>

                    <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        Update Brand Segment
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

<div class="mt-5 max-w-4xl rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">

    <span class="font-semibold">Important:</span>
    Changing this Segment's name or code updates its classification wherever it is displayed.
    Existing Brand records, legacy Product references, and Product–Brand associations remain unchanged.

</div>

@endsection