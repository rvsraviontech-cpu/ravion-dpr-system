@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $selectedBrandSegmentId = old(
        'brand_segment_id',
        $brandMaster->brand_segment_id
    );
@endphp

<div class="mx-auto max-w-6xl">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Material Brand
            </h1>

            <p class="mt-1 text-gray-500">
                Manage the Brand identity, Brand Segment and canonical Product associations.
            </p>
        </div>

        <a href="{{ route('brand-masters.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

            <p class="mb-2 font-semibold">
                Please correct the following:
            </p>

            <ul class="ml-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(!$brandMaster->brand_segment_id)
        <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">

            <div class="font-semibold">
                Brand Segment classification required
            </div>

            <p class="mt-1 text-sm leading-6">
                This Brand was created before the Brand Segment architecture was introduced.
                Select the appropriate Brand Segment below before updating the Brand.
                Existing legacy classification data will be preserved for historical compatibility.
            </p>

        </div>
    @endif

    <form method="POST"
          action="{{ route('brand-masters.update', $brandMaster) }}">

        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="mb-5">
                <h2 class="text-xl font-bold text-gray-800">
                    Brand Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Brand Segment identifies the commercial category of the Brand. Product applicability is managed separately below.
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
                                {{ (string) $selectedBrandSegmentId === (string) $segment->id ? 'selected' : '' }}>
                                {{ $segment->segment_name }}
                            </option>
                        @endforeach

                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        The Brand name is unique within its Brand Segment.
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
                           value="{{ old('brand_name', $brandMaster->brand_name) }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: Ambuja, UltraTech, JSW, Astral"
                           maxlength="255"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}"
                           for="brand_code">
                        Brand Code
                    </label>

                    <input type="text"
                           id="brand_code"
                           name="brand_code"
                           value="{{ old('brand_code', $brandMaster->brand_code) }}"
                           class="{{ $inputClass }}"
                           placeholder="Optional internal code"
                           maxlength="100">
                </div>

                <div>
                    <label class="{{ $labelClass }}"
                           for="sequence">
                        Display Sequence
                    </label>

                    <input type="number"
                           id="sequence"
                           name="sequence"
                           value="{{ old('sequence', $brandMaster->sequence) }}"
                           min="0"
                           class="{{ $inputClass }}">
                </div>

                <div class="flex items-center">
                    <label class="inline-flex items-center gap-3">

                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $brandMaster->is_active) ? 'checked' : '' }}>

                        <span class="text-sm font-semibold text-gray-700">
                            Active
                        </span>

                    </label>
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
                              placeholder="Optional notes about this Brand">{{ old('remarks', $brandMaster->remarks) }}</textarea>
                </div>

            </div>

        </div>

        @if(
            $brandMaster->material_type_id
            || $brandMaster->material_category_id
            || $brandMaster->activity_id
        )
            <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-5">

                <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">

                    <div>
                        <h3 class="font-bold text-gray-800">
                            Legacy Classification
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Historical information retained for compatibility. These fields are read-only and are not used to define the canonical Brand identity.
                        </p>
                    </div>

                    <span class="inline-flex self-start rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                        Historical
                    </span>

                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Legacy Material Type
                        </div>

                        <div class="mt-1 font-semibold text-gray-800">
                            {{ $brandMaster->materialType?->material_type_name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Legacy Material Group
                        </div>

                        <div class="mt-1 font-semibold text-gray-800">
                            {{ $brandMaster->materialType?->material_group ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Legacy Material Type ID
                        </div>

                        <div class="mt-1 font-semibold text-gray-800">
                            {{ $brandMaster->material_type_id ?? '-' }}
                        </div>
                    </div>

                </div>

            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                Update Brand
            </button>

            <a href="{{ route('brand-masters.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>

        </div>

    </form>

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5">
            <h2 class="text-xl font-bold text-gray-800">
                Canonical Product Associations
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Manage the canonical Products that may use {{ $brandMaster->brand_name }}.
                These associations are shared with Product Master and operational material dropdowns.
            </p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">

            <table class="min-w-full text-sm">

                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">

                    <tr>
                        <th class="px-4 py-3 text-left">
                            Product
                        </th>

                        <th class="px-4 py-3 text-left">
                            Product Group / Type
                        </th>

                        <th class="px-4 py-3 text-center">
                            Preferred
                        </th>

                        <th class="px-4 py-3 text-center">
                            Sort Order
                        </th>

                        <th class="px-4 py-3 text-center">
                            Mapping
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

                    @forelse(
                        $brandMaster->productMappings->sortBy([
                            ['sort_order', 'asc'],
                            ['id', 'asc'],
                        ]) as $mapping
                    )

                        <tr class="align-top hover:bg-gray-50">

                            <td class="px-4 py-3">

                                <div class="font-semibold text-gray-800">
                                    {{ $mapping->product?->material_type_name ?? '-' }}
                                </div>

                                @if($mapping->product?->material_type_code)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $mapping->product->material_type_code }}
                                    </div>
                                @endif

                            </td>

                            <td class="px-4 py-3 text-gray-700">

                                <div>
                                    {{ $mapping->product?->productGroup?->name
                                        ?? $mapping->product?->material_group
                                        ?? '-' }}
                                </div>

                                @if($mapping->product?->productType?->name)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $mapping->product->productType->name }}
                                    </div>
                                @endif

                            </td>

                            <td class="px-4 py-3 text-center">

                                @if($mapping->is_preferred)

                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                        Yes
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        No
                                    </span>

                                @endif

                            </td>

                            <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                {{ $mapping->sort_order }}
                            </td>

                            <td class="px-4 py-3 text-center">

                                @if($mapping->is_active)

                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                        Active
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Inactive
                                    </span>

                                @endif

                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $mapping->remarks ?: '-' }}
                            </td>

                            <td class="px-4 py-3">

                                @if(auth()->user()?->hasPermission('materials.manage'))

                                    <div class="min-w-[230px] space-y-3">

                                        <form method="POST"
                                              action="{{ route('brand-masters.products.update', [$brandMaster, $mapping]) }}"
                                              class="space-y-2">

                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-2 gap-2">

                                                <input type="number"
                                                       name="sort_order"
                                                       value="{{ $mapping->sort_order }}"
                                                       min="0"
                                                       class="{{ $inputClass }}"
                                                       aria-label="Sort order">

                                                <label class="flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700">

                                                    <input type="checkbox"
                                                           name="is_preferred"
                                                           value="1"
                                                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                           {{ $mapping->is_preferred ? 'checked' : '' }}>

                                                    Preferred

                                                </label>

                                            </div>

                                            <input type="text"
                                                   name="remarks"
                                                   value="{{ $mapping->remarks }}"
                                                   class="{{ $inputClass }}"
                                                   placeholder="Mapping remarks">

                                            <button type="submit"
                                                    class="w-full rounded bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                                Update Association
                                            </button>

                                        </form>

                                        <form method="POST"
                                              action="{{ route('brand-masters.products.toggle-status', [$brandMaster, $mapping]) }}">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    onclick="return confirm('Change this Product association status?')"
                                                    class="w-full rounded px-3 py-2 text-xs font-semibold text-white
                                                    {{ $mapping->is_active
                                                        ? 'bg-red-600 hover:bg-red-700'
                                                        : 'bg-green-600 hover:bg-green-700' }}">

                                                {{ $mapping->is_active
                                                    ? 'Deactivate Association'
                                                    : 'Activate Association' }}

                                            </button>

                                        </form>

                                    </div>

                                @else

                                    <span class="text-xs text-gray-400">
                                        View only
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="px-6 py-8 text-center text-gray-500">
                                No canonical Products are associated with this Brand yet.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if(auth()->user()?->hasPermission('materials.manage'))

            <div class="mt-6 border-t border-gray-200 pt-6">

                <h3 class="text-base font-bold text-gray-800">
                    Associate Product
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Select an existing active canonical Product. This does not create a new Product or Brand.
                </p>

                @if($availableProducts->isNotEmpty())

                    <form method="POST"
                          action="{{ route('brand-masters.products.store', $brandMaster) }}"
                          class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-12">

                        @csrf

                        <div class="md:col-span-5">

                            <label class="{{ $labelClass }}">
                                Canonical Product
                                <span class="text-red-500">*</span>
                            </label>

                            <select name="material_type_id"
                                    class="{{ $inputClass }}"
                                    required>

                                <option value="">
                                    Select Product
                                </option>

                                @foreach($availableProducts as $product)

                                    <option value="{{ $product->id }}"
                                        {{ (string) old('material_type_id') === (string) $product->id ? 'selected' : '' }}>

                                        {{ $product->material_type_name }}

                                        @if($product->material_type_code)
                                            — {{ $product->material_type_code }}
                                        @endif

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="md:col-span-2">

                            <label class="{{ $labelClass }}">
                                Sort Order
                            </label>

                            <input type="number"
                                   name="sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   class="{{ $inputClass }}">

                        </div>

                        <div class="flex items-end md:col-span-2">

                            <label class="flex h-[42px] w-full items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm font-semibold text-gray-700">

                                <input type="checkbox"
                                       name="is_preferred"
                                       value="1"
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ old('is_preferred') ? 'checked' : '' }}>

                                Preferred

                            </label>

                        </div>

                        <div class="md:col-span-3">

                            <label class="{{ $labelClass }}">
                                Remarks
                            </label>

                            <input type="text"
                                   name="remarks"
                                   value="{{ old('remarks') }}"
                                   class="{{ $inputClass }}"
                                   placeholder="Optional">

                        </div>

                        <div class="md:col-span-12">

                            <button type="submit"
                                    class="rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white hover:bg-blue-700">
                                + Associate Product
                            </button>

                        </div>

                    </form>

                @else

                    <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        All available active canonical Products are already associated with this Brand.
                    </div>

                @endif

            </div>

        @endif

    </div>

</div>

@endsection