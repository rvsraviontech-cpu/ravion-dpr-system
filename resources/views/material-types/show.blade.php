@extends('layouts.app')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $materialType->material_type_name }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Product Master details, catalogue classification and usage references.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(auth()->user()?->hasPermission('materials.manage'))
                <a href="{{ route('material-types.edit', $materialType) }}"
                   class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                    Edit Product
                </a>
            @endif

            <a href="{{ route('material-types.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back
            </a>
        </div>
    </div>

    {{-- Main Product Information --}}
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="mb-4 flex flex-wrap items-center gap-2">
            @if($materialType->master_status === 'Approved')
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                    Approved
                </span>
            @else
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ $materialType->master_status ?? 'Review' }}
                </span>
            @endif

            @if($materialType->is_active)
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                    Active
                </span>
            @else
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                    Inactive
                </span>
            @endif

            @if($materialType->is_legacy)
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                    Legacy
                </span>
            @elseif($materialType->catalogue_source_code)
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    Catalogue
                </span>
            @else
                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                    Manual
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2 lg:grid-cols-4">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Product Name
                </p>
                <p class="mt-1 font-semibold text-gray-900">
                    {{ $materialType->material_type_name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Product Group
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->productGroup?->group_name ?? $materialType->material_group ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Product Type
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->productType?->type_name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Default Unit
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->unit?->unit_code ?? $materialType->unit?->unit_name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Inventory Type
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->inventory_type ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Internal Code
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->material_type_code ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Catalogue Code
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->catalogue_source_code ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Sequence
                </p>
                <p class="mt-1 text-gray-800">
                    {{ $materialType->sequence ?? 0 }}
                </p>
            </div>

            <div class="md:col-span-2 lg:col-span-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Remarks
                </p>
                <p class="mt-1 whitespace-pre-line text-gray-800">
                    {{ $materialType->remarks ?: '-' }}
                </p>
            </div>

        </div>

    </div>

    {{-- Supporting Masters --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- Aliases --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="font-bold text-gray-900">
                    Search Aliases
                </h2>
            </div>

            <div class="p-4">
                @forelse($materialType->searchAliases as $alias)
                    <div class="mb-2 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                        <span class="text-sm text-gray-800">
                            {{ $alias->alias }}
                        </span>

                        @if($alias->is_active)
                            <span class="text-xs font-semibold text-green-700">
                                Active
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        No search aliases.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Specifications --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="font-bold text-gray-900">
                    Specifications
                </h2>
            </div>

            <div class="p-4">
                @forelse($materialType->specifications as $specification)
                    <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-800">
                        {{ $specification->specification_name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        No canonical specifications.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Grades --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
                <h2 class="font-bold text-gray-900">
                    Grades
                </h2>
            </div>

            <div class="p-4">
                @forelse($materialType->grades as $grade)
                    <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-800">
                        {{ $grade->grade_name }}
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        No canonical grades.
                    </p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Brand Associations --}}
    <div class="mt-5 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-bold text-gray-900">Brand Associations</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Canonical Brand Master associations available for this Product. Mapping status is independent of the global Brand status.
                </p>
            </div>

            @if(auth()->user()?->hasPermission('materials.manage'))
                @if($availableBrands->isNotEmpty())
                    <details>
                        <summary class="cursor-pointer list-none rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                            Assign Brand
                        </summary>
                        <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <form method="POST" action="{{ route('material-types.brands.store', $materialType) }}"
                                  class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                @csrf
                                <div class="md:col-span-5">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Brand</label>
                                    <select name="brand_master_id" required
                                            class="w-full rounded-lg border-gray-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                        <option value="">Select Brand</option>
                                        @foreach($availableBrands as $brand)
                                            <option value="{{ $brand->id }}" @selected((string) old('brand_master_id') === (string) $brand->id)>
                                                {{ $brand->brand_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_master_id')
                                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Sort Order</label>
                                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>

                                <div class="flex items-end md:col-span-2">
                                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                        <input type="hidden" name="is_preferred" value="0">
                                        <input type="checkbox" name="is_preferred" value="1" @checked(old('is_preferred'))
                                               class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                        Preferred
                                    </label>
                                </div>

                                <div class="md:col-span-12">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Remarks</label>
                                    <textarea name="remarks" rows="2" maxlength="2000"
                                              class="w-full rounded-lg border-gray-300 text-sm"
                                              placeholder="Optional mapping remarks">{{ old('remarks') }}</textarea>
                                </div>

                                <div class="md:col-span-12">
                                    <button type="submit"
                                            class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                                        Save Brand Association
                                    </button>
                                </div>
                            </form>
                        </div>
                    </details>
                @else
                    <span class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-500">
                        All active Brands are already associated
                    </span>
                @endif
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Brand</th>
                        <th class="px-4 py-3 text-center">Preferred</th>
                        <th class="px-4 py-3 text-center">Sort Order</th>
                        <th class="px-4 py-3 text-center">Mapping Status</th>
                        <th class="px-4 py-3 text-center">Brand Status</th>
                        <th class="px-4 py-3 text-left">Remarks</th>
                        @if(auth()->user()?->hasPermission('materials.manage'))
                            <th class="px-4 py-3 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($materialType->productBrandMappings->sortBy('sort_order') as $mapping)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $mapping->brand?->brand_name ?? 'Missing Brand' }}</div>
                                @if($mapping->brand?->brand_code)
                                    <div class="mt-0.5 text-xs text-gray-500">{{ $mapping->brand->brand_code }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($mapping->is_preferred)
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Yes</span>
                                @else
                                    <span class="text-gray-500">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $mapping->sort_order ?? 0 }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $mapping->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $mapping->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $mapping->brand?->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $mapping->brand?->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-gray-700">{{ $mapping->remarks ?: '-' }}</td>

                            @if(auth()->user()?->hasPermission('materials.manage'))
                                <td class="px-4 py-3 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <details class="text-left">
                                            <summary class="cursor-pointer list-none rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                                Edit
                                            </summary>
                                            <div class="mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 shadow-lg">
                                                <form method="POST" action="{{ route('material-types.brands.update', [$materialType, $mapping]) }}"
                                                      class="space-y-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label class="mb-1 block text-xs font-semibold text-gray-600">Sort Order</label>
                                                        <input type="number" min="0" name="sort_order" value="{{ $mapping->sort_order ?? 0 }}"
                                                               class="w-full rounded-lg border-gray-300 text-sm">
                                                    </div>
                                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                                        <input type="hidden" name="is_preferred" value="0">
                                                        <input type="checkbox" name="is_preferred" value="1" @checked($mapping->is_preferred)
                                                               class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                                        Preferred Brand
                                                    </label>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-semibold text-gray-600">Remarks</label>
                                                        <textarea name="remarks" rows="2" maxlength="2000"
                                                                  class="w-full rounded-lg border-gray-300 text-sm">{{ $mapping->remarks }}</textarea>
                                                    </div>
                                                    <button type="submit"
                                                            class="w-full rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-700">
                                                        Update Association
                                                    </button>
                                                </form>
                                            </div>
                                        </details>

                                        <form method="POST" action="{{ route('material-types.brands.toggle-status', [$materialType, $mapping]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $mapping->is_active ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                {{ $mapping->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center">
                                <div class="font-medium text-gray-700">No Brand Associations yet.</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Assign existing Brand Masters to this Product to make them available in canonical material workflows.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Variants --}}
    <div class="mt-5 rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="font-bold text-gray-900">
                Product Variants
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Variant</th>
                        <th class="px-4 py-3 text-left">Size / Dimension</th>
                        <th class="px-4 py-3 text-left">Finish</th>
                        <th class="px-4 py-3 text-left">Colour</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($materialType->variants as $variant)
                        <tr>
                            <td class="px-4 py-3">
                                {{ $variant->variant_name ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $variant->size_dimension ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $variant->finish ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $variant->colour_shade ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $variant->is_active ? 'Active' : 'Inactive' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-4 py-8 text-center text-gray-500">
                                No canonical variants.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

    {{-- Usage Mappings --}}
    <div class="mt-5 rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="font-bold text-gray-900">
                Product Usage Mapping
            </h2>

            <p class="mt-1 text-xs text-gray-500">
                Shows where this Product may be used in construction execution.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Source</th>
                        <th class="px-4 py-3 text-left">Usage Type</th>
                        <th class="px-4 py-3 text-center">Primary</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($materialType->usageMappings as $usage)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    {{ $usage->source_name ?? $usage->source_code ?? '-' }}
                                </div>

                                @if($usage->source_code)
                                    <div class="text-xs text-gray-500">
                                        {{ $usage->source_code }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                {{ $usage->usage_type ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $usage->is_primary ? 'Yes' : 'No' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $usage->is_active ? 'Active' : 'Inactive' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-4 py-8 text-center text-gray-500">
                                No Product Usage Mappings.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection