
@extends('layouts.app')

@section('content')

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            Add Brand Segment
        </h1>

        <p class="mt-1 text-gray-500">
            Create a reusable classification for Brand identities.
            Products are associated with individual Brands separately.
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
            Segment Code and Segment Name must each be unique.
        </p>

    </div>

    <form method="POST"
          action="{{ route('brand-segments.store') }}">

        @csrf

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
                       value="{{ old('segment_code') }}"
                       placeholder="Example: ELE"
                       class="w-full rounded-lg border-gray-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-1 text-xs text-gray-500">
                    Short identifier, such as ELE, PLB or BLD.
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
                       value="{{ old('segment_name') }}"
                       placeholder="Example: Electrical"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-1 text-xs text-gray-500">
                    Commercial category used to distinguish Brand identities.
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
                       value="{{ old('sort_order', 0) }}"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                <p class="mt-1 text-xs text-gray-500">
                    Lower numbers appear first in Brand Segment dropdowns.
                </p>

                @error('sort_order')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- Initial status --}}
            <div>

                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Initial Status
                </label>

                <div class="flex min-h-[42px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3">

                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                        Active
                    </span>

                    <span class="ml-3 text-xs text-gray-500">
                        New Brand Segments are created as Active.
                    </span>

                </div>

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
                          class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('remarks') }}</textarea>

                @error('remarks')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </div>

        </div>

        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">

            <div class="flex flex-wrap items-center justify-end gap-3">

                <a href="{{ route('brand-segments.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Cancel
                </a>

                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Save Brand Segment
                </button>

            </div>

        </div>

    </form>

</div>

<div class="mt-5 max-w-4xl rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">

    <span class="font-semibold">Important:</span>
    A Brand Segment classifies the Brand identity only.
    It does not automatically restrict which Products a Brand may be associated with.
    Product applicability is managed through the Product–Brand associations.

</div>

@endsection