@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp

<div class="mx-auto max-w-5xl">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Add Material Brand
            </h1>

            <p class="mt-1 text-gray-500">
                Create a reusable Brand identity. Products can be associated with the Brand after it is created.
            </p>
        </div>

        <a href="{{ route('brand-masters.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

            <ul class="ml-5 list-disc">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>
    @endif

    <form method="POST"
          action="{{ route('brand-masters.store') }}">

        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="mb-5">
                <h2 class="text-xl font-bold text-gray-800">
                    Brand Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Brand Segment identifies the commercial category of the Brand. Product applicability is managed separately.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label class="{{ $labelClass }}"
                           for="brand_segment_id">
                        Brand Segment
                        <span class="text-red-500">*</span>
                    </label>

                    <select id="brand_segment_id"
                            name="brand_segment_id"
                            class="{{ $inputClass }}"
                            required>

                        <option value="">
                            Select Brand Segment
                        </option>

                        @foreach($brandSegments as $segment)
                            <option value="{{ $segment->id }}"
                                {{ (string) old('brand_segment_id') === (string) $segment->id ? 'selected' : '' }}>
                                {{ $segment->segment_name }}
                            </option>
                        @endforeach

                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        Example: Building Materials, Electrical, Plumbing &amp; Sanitary, HVAC &amp; Ventilation.
                    </p>
                </div>

                <div>
                    <label class="{{ $labelClass }}"
                           for="brand_name">
                        Brand Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           id="brand_name"
                           name="brand_name"
                           value="{{ old('brand_name') }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: Ambuja, UltraTech, JSW, Astral"
                           maxlength="255"
                           required>

                    <p class="mt-1 text-xs text-gray-500">
                        The same Brand name may exist in another Brand Segment when they represent different commercial Brand identities.
                    </p>
                </div>

                <div>
                    <label class="{{ $labelClass }}"
                           for="brand_code">
                        Brand Code
                    </label>

                    <input type="text"
                           id="brand_code"
                           name="brand_code"
                           value="{{ old('brand_code') }}"
                           class="{{ $inputClass }}"
                           placeholder="Optional internal code"
                           maxlength="100">

                    <p class="mt-1 text-xs text-gray-500">
                        Optional internal reference code for this Brand.
                    </p>
                </div>

                <div>
                    <label class="{{ $labelClass }}"
                           for="sequence">
                        Display Sequence
                    </label>

                    <input type="number"
                           id="sequence"
                           name="sequence"
                           value="{{ old('sequence', 0) }}"
                           min="0"
                           class="{{ $inputClass }}">

                    <p class="mt-1 text-xs text-gray-500">
                        Lower numbers appear first when Brands are ordered.
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}"
                           for="remarks">
                        Remarks
                    </label>

                    <textarea id="remarks"
                              name="remarks"
                              rows="4"
                              class="{{ $inputClass }}"
                              maxlength="2000"
                              placeholder="Optional notes about this Brand">{{ old('remarks') }}</textarea>
                </div>

            </div>

        </div>

        <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4">

            <div class="font-semibold text-blue-900">
                Product Associations
            </div>

            <p class="mt-1 text-sm leading-6 text-blue-800">
                After saving the Brand, open its Edit page to associate one or more Products with it. A Brand is not restricted to a single Product.
            </p>

        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                Save Brand
            </button>

            <a href="{{ route('brand-masters.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection