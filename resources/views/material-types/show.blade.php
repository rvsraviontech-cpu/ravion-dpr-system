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

    {{-- Bible classification: authoritative display; legacy columns remain untouched in database. --}}
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Material Bible Classification</h2>
                <p class="mt-1 text-xs text-gray-500">Trade Category → Main Category → Sub Category → Item</p>
            </div>
            <div class="flex gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $materialType->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $materialType->is_active ? 'Active' : 'Inactive' }}</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $materialType->master_status ?? 'Review' }}</span>
            </div>
        </div>
        @forelse(($biblePaths ?? collect()) as $path)
            <div class="mb-3 grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <div><p class="text-xs font-semibold uppercase text-gray-500">Trade Category</p><p class="mt-1 text-sm font-medium text-gray-900">{{ $path->trade_name }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">Main Category</p><p class="mt-1 text-sm font-medium text-gray-900">{{ $path->main_name }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">Sub Category</p><p class="mt-1 text-sm font-medium text-gray-900">{{ $path->sub_name }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-gray-500">Item</p><p class="mt-1 text-sm font-bold text-gray-900">{{ $materialType->material_type_name }}</p></div>
            </div>
        @empty
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">No active Material Bible classification mapping exists for this product. Historical product details remain preserved.</div>
        @endforelse
        <div class="mt-4 grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><p class="text-xs font-semibold uppercase text-gray-500">Default Unit</p><p class="mt-1 text-sm text-gray-900">{{ $materialType->unit?->unit_code ?? $materialType->unit?->unit_name ?? '-' }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-gray-500">Item Nature / Inventory Type</p><p class="mt-1 text-sm text-gray-900">{{ $materialType->inventory_type ?? '-' }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-gray-500">Internal Code</p><p class="mt-1 text-sm text-gray-900">{{ $materialType->material_type_code ?? '-' }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-gray-500">Catalogue Source</p><p class="mt-1 text-sm text-gray-900">{{ $materialType->catalogue_source_code ?? '-' }}</p></div>
        </div>
        @if($materialType->remarks)
            <div class="mt-4 border-t border-gray-100 pt-3"><p class="text-xs font-semibold uppercase text-gray-500">Product Remarks</p><p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $materialType->remarks }}</p></div>
        @endif
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

            @php
                $allAliases = $materialType->searchAliases;
                $visibleAliases = $allAliases->take(8);
                $hiddenAliases = $allAliases->slice(8);
            @endphp
            <div class="p-4">
                @forelse($visibleAliases as $alias)
                    <div class="mb-2 flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2">
                        <span class="min-w-0 break-words text-sm text-gray-800">{{ $alias->alias }}</span>
                        @if($alias->is_active)<span class="shrink-0 text-xs font-semibold text-green-700">Active</span>@endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No search aliases.</p>
                @endforelse
                @if($hiddenAliases->isNotEmpty())
                    <details class="mt-3 rounded-lg border border-gray-200 bg-white">
                        <summary class="cursor-pointer px-3 py-2 text-sm font-semibold text-sky-700">Show {{ $hiddenAliases->count() }} more aliases</summary>
                        <div class="max-h-72 overflow-y-auto border-t border-gray-100 p-3">
                            @foreach($hiddenAliases as $alias)
                                <div class="mb-2 flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2">
                                    <span class="min-w-0 break-words text-sm text-gray-800">{{ $alias->alias }}</span>
                                    @if($alias->is_active)<span class="shrink-0 text-xs font-semibold text-green-700">Active</span>@endif
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
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
        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="font-bold text-gray-900">Brand Associations</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Canonical Brand Master associations available for this Product. Mapping status is independent of the global Brand status.
                </p>
            </div>

            @if(auth()->user()?->hasPermission('materials.manage'))
                @if($availableBrands->isNotEmpty())
                    <a href="#assign-brand-form"
                       onclick="document.getElementById('assign-brand-form').hidden = !document.getElementById('assign-brand-form').hidden; return false;"
                       class="inline-flex shrink-0 items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white"
                       style="background-color: #10212F; color: #ffffff;">
                        Assign Brand
                    </a>
                @else
                    <span class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-500">
                        All active Brands are already associated
                    </span>
                @endif
            @endif
        </div>

        @if(auth()->user()?->hasPermission('materials.manage') && $availableBrands->isNotEmpty())
            <div id="assign-brand-form" class="border-b border-gray-200 bg-gray-50 px-4 py-4" hidden>
                <form method="POST" action="{{ route('material-types.brands.store', $materialType) }}"
                      class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12">
                    @csrf
                    {{-- Segment is a dropdown filter only; the submitted association remains Brand-to-Product. --}}
                    <div class="min-w-0 sm:col-span-1 lg:col-span-3">
                        <label for="assign-brand-segment" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Brand Segment</label>
                        <select id="assign-brand-segment"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Select Brand Segment</option>
                            @foreach($availableBrands->filter(fn ($brand) => $brand->brand_segment_id !== null)->groupBy('brand_segment_id') as $segmentId => $segmentBrands)
                                <option value="{{ $segmentId }}" @selected((string) old('brand_master_id') !== '' && (string) $availableBrands->firstWhere('id', old('brand_master_id'))?->brand_segment_id === (string) $segmentId)>
                                    {{ $segmentBrands->first()?->segment?->segment_name ?? 'Unnamed Segment' }}
                                </option>
                            @endforeach
                            @if($availableBrands->contains(fn ($brand) => $brand->brand_segment_id === null))
                                <option value="legacy" @selected((string) old('brand_master_id') !== '' && $availableBrands->firstWhere('id', old('brand_master_id'))?->brand_segment_id === null && $availableBrands->firstWhere('id', old('brand_master_id')) !== null)>
                                    Legacy / Unclassified
                                </option>
                            @endif
                        </select>
                    </div>

                    <div class="min-w-0 sm:col-span-1 lg:col-span-6">
                        <label for="assign-brand-select" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Brand</label>
                        <select id="assign-brand-select" name="brand_master_id" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Select Brand</option>
                            @foreach($availableBrands as $brand)
                                <option value="{{ $brand->id }}"
                                        data-brand-segment="{{ $brand->brand_segment_id ?? 'legacy' }}"
                                        @selected((string) old('brand_master_id') === (string) $brand->id)>
                                    {{ $brand->brand_name }} — {{ $brand->segment?->segment_name ?? 'Legacy / Unclassified' }}
                                </option>
                            @endforeach
                        </select>
                        <p id="assign-brand-help" class="mt-1 text-xs text-gray-500" aria-live="polite">Select a Brand Segment to view available Brands.</p>
                        @error('brand_master_id')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="min-w-0 lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Sort Order</label>
                        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}"
                               class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div class="flex items-end lg:col-span-1">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                            <input type="hidden" name="is_preferred" value="0">
                            <input type="checkbox" name="is_preferred" value="1" @checked(old('is_preferred'))
                                   class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                            Preferred
                        </label>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-12">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Remarks</label>
                        <textarea name="remarks" rows="2" maxlength="2000"
                                  class="w-full rounded-lg border-gray-300 text-sm"
                                  placeholder="Optional mapping remarks">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-12">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white"
                                style="background-color: #10212F; color: #ffffff;">
                            Save Brand Association
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if(auth()->user()?->hasPermission('materials.manage') && $availableBrands->isNotEmpty())
            <script>
                (() => {
                    const segment = document.getElementById('assign-brand-segment');
                    const brand = document.getElementById('assign-brand-select');
                    const help = document.getElementById('assign-brand-help');
                    if (!segment || !brand || !help) return;

                    function filterBrands(resetSelection = false) {
                        const selectedSegment = segment.value;
                        if (resetSelection) brand.value = '';
                        let count = 0;
                        for (const option of brand.options) {
                            if (!option.value) continue;
                            const visible = selectedSegment !== '' && option.dataset.brandSegment === selectedSegment;
                            option.hidden = !visible;
                            option.disabled = !visible;
                            if (visible) count++;
                        }
                        if (!selectedSegment) {
                            brand.value = '';
                            brand.disabled = true;
                            help.textContent = 'Select a Brand Segment to view available Brands.';
                        } else {
                            brand.disabled = count === 0;
                            help.textContent = count === 0
                                ? 'No available Brands in this segment.'
                                : `${count} available Brand${count === 1 ? '' : 's'} in this segment.`;
                        }
                    }

                    segment.addEventListener('change', () => filterBrands(true));
                    filterBrands();
                })();
            </script>
        @endif

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
                                <div class="font-semibold text-gray-900">
    {{ $mapping->brand?->brand_name ?? 'Missing Brand' }}
</div>

@if($mapping->brand)
    <div class="mt-0.5 text-xs text-gray-500">
        {{ $mapping->brand->segment?->segment_name ?? 'Legacy / Unclassified' }}
        @if($mapping->brand->brand_code)
            · {{ $mapping->brand->brand_code }}
        @endif
    </div>
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
                                                            class="w-full rounded-lg px-3 py-2 text-xs font-semibold text-white"
                                                             style="background-color: #10212F; color: #ffffff;">
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

    {{-- Bible row-level display: F and G remain distinct; imported remarks are read-only. --}}
    <div id="bible-variants" class="mt-5 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-4 py-4">
            <div>
                <h2 class="font-bold text-gray-900">Material Bible — Sub Items &amp; Specifications</h2>
                <p class="mt-1 text-xs text-gray-500">Workbook columns F and G are displayed separately. Imported metadata is available under each row.</p>
            </div>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $materialType->variants->count() }} variants</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600"><tr>
                    <th class="px-4 py-3 text-left">Sub Item / Variant</th>
                    <th class="px-4 py-3 text-left">Size / Grade / Finish / Specification</th>
                    <th class="px-4 py-3 text-left">Unit</th>
                    <th class="px-4 py-3 text-left">Bible Category</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Details</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($materialType->variants->sortBy('sort_order') as $variant)
                        @php
                            $fullVariantName = trim((string) $variant->variant_name);
                            $sizeValue = trim((string) $variant->size_dimension);
                            $suffix = $sizeValue !== '' ? ' - '.$sizeValue : '';
                            $subItemValue = $suffix !== '' && \Illuminate\Support\Str::endsWith($fullVariantName, $suffix)
                                ? trim(substr($fullVariantName, 0, -strlen($suffix)))
                                : ($sizeValue !== '' && $fullVariantName === $sizeValue ? '' : $fullVariantName);
                            $variantBiblePaths = ($bibleVariantMappings ?? collect())->get($variant->id, collect());
                            $metadata = (string) ($variant->remarks ?? '');
                            $nature = null;
                            $application = null;
                            $fastMoving = null;
                            $controlled = null;
                            // Parse only known labelled importer fields; preserve original remarks below.
                            if (preg_match('/(?:^|;\s*)Nature:\s*(.*?)(?=;\s*(?:Application|Fast Moving|Controlled):|$)/s', $metadata, $match)) $nature = trim($match[1]);
                            if (preg_match('/(?:^|;\s*)Application:\s*(.*?)(?=;\s*(?:Fast Moving|Controlled):|$)/s', $metadata, $match)) $application = trim($match[1]);
                            if (preg_match('/(?:^|;\s*)Fast Moving:\s*(.*?)(?=;\s*Controlled:|$)/s', $metadata, $match)) $fastMoving = trim($match[1]);
                            if (preg_match('/(?:^|;\s*)Controlled:\s*(.*)$/s', $metadata, $match)) $controlled = trim($match[1]);
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $subItemValue !== '' ? $subItemValue : '—' }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $sizeValue !== '' ? $sizeValue : '—' }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $variant->unit?->unit_code ?? $variant->unit?->unit_name ?? $materialType->unit?->unit_code ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                @forelse($variantBiblePaths->unique('sub_id') as $mappedPath)
                                    <div>{{ $mappedPath->trade_name }} / {{ $mappedPath->main_name }} / {{ $mappedPath->sub_name }}</div>
                                @empty
                                    <span class="text-gray-400">Product-level classification</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $variant->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $variant->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <details class="group relative inline-block text-left">
                                    <summary class="cursor-pointer whitespace-nowrap rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-gray-50">View details</summary>
                                    <div class="mt-2 w-72 max-w-[75vw] rounded-lg border border-gray-200 bg-gray-50 p-3 text-left shadow-sm sm:w-96">
                                        <dl class="space-y-2 text-xs">
                                            <div><dt class="font-semibold text-gray-500">Item Nature</dt><dd class="mt-0.5 break-words text-gray-800">{{ $nature ?: 'Not separately recorded' }}</dd></div>
                                            <div><dt class="font-semibold text-gray-500">Used In / Application</dt><dd class="mt-0.5 break-words text-gray-800">{{ $application ?: 'Not separately recorded' }}</dd></div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div><dt class="font-semibold text-gray-500">Daily Use / Fast Moving</dt><dd class="mt-0.5 text-gray-800">{{ $fastMoving ?: 'Not recorded' }}</dd></div>
                                                <div><dt class="font-semibold text-gray-500">High Value / Controlled</dt><dd class="mt-0.5 text-gray-800">{{ $controlled ?: 'Not recorded' }}</dd></div>
                                            </div>
                                            <div><dt class="font-semibold text-gray-500">Variant ID / Code</dt><dd class="mt-0.5 break-all text-gray-800">#{{ $variant->id }} · {{ $variant->variant_code ?: '—' }}</dd></div>
                                            @if($metadata !== '')
                                                <div class="border-t border-gray-200 pt-2"><dt class="font-semibold text-gray-500">Original Imported / Variant Remarks</dt><dd class="mt-1 whitespace-pre-line break-words text-gray-700">{{ $metadata }}</dd></div>
                                            @endif
                                        </dl>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No variant rows are available for this product.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-4 py-3 text-xs text-gray-500">Metadata is read from existing variant remarks. Missing values are not inferred; original remarks remain accessible for verification.</div>
    </div>

    <details class="mt-5 rounded-xl border border-gray-200 bg-white shadow-sm">
        <summary class="cursor-pointer px-4 py-3 font-semibold text-gray-800">Variant Administration — expand to view existing create / edit controls</summary>
    {{-- Product Variants & Attributes --}}
    @php
        $canManageVariants = auth()->user()?->hasPermission('materials.manage');
        $variantUnits = \App\Models\UnitMaster::query()->where('is_active', true)->orderBy('unit_name')->get();
        $variantSpecifications = $materialType->specifications->where('is_active', true)->sortBy('specification_name');
        $variantGrades = $materialType->grades->where('is_active', true)->sortBy('grade_name');
    @endphp
    <div id="product-variants" class="mt-5 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
            <div>
                <h2 class="font-bold text-gray-900">Variant Administration (Existing Forms)</h2>
                <p class="mt-1 text-xs text-gray-500">Administrative editing for existing variants. Imported Bible records should not be modified without verification.</p>
            </div>
            @if($canManageVariants)
                <a href="#new-variant" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:#10212F">Add Variant</a>
            @endif
        </div>

        @if($errors->any())
            <div class="mx-4 mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                Please review the form fields: {{ $errors->first() }}
            </div>
        @endif

        @if($canManageVariants)
            <details id="new-variant" class="mx-4 mt-4 rounded-lg border border-sky-200 bg-sky-50 p-4" @if($errors->any()) open @endif>
                <summary class="cursor-pointer font-semibold text-gray-900">Create a new variant</summary>
                <form method="POST" action="{{ route('material-types.variants.store', ['material_type' => $materialType->id]) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Variant Name *</label><input required name="variant_name" value="{{ old('variant_name') }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Variant Code</label><input name="variant_code" value="{{ old('variant_code') }}" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Specification</label><select name="material_specification_id" class="w-full rounded-lg border-gray-300 text-sm"><option value="">Not applicable</option>@foreach($variantSpecifications as $spec)<option value="{{ $spec->id }}" @selected((string)old('material_specification_id') === (string)$spec->id)>{{ $spec->specification_name }}</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Grade</label><select name="material_grade_id" class="w-full rounded-lg border-gray-300 text-sm"><option value="">Not applicable</option>@foreach($variantGrades as $grade)<option value="{{ $grade->id }}" @selected((string)old('material_grade_id') === (string)$grade->id)>{{ $grade->grade_name }}</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Unit</label><select name="unit_master_id" class="w-full rounded-lg border-gray-300 text-sm"><option value="">Use product default</option>@foreach($variantUnits as $unit)<option value="{{ $unit->id }}" @selected((string)old('unit_master_id') === (string)$unit->id)>{{ $unit->unit_name }} ({{ $unit->unit_code }})</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Size / Dimension</label><input name="size_dimension" value="{{ old('size_dimension') }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Finish</label><input name="finish" value="{{ old('finish') }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Colour / Shade</label><input name="colour_shade" value="{{ old('colour_shade') }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Search Aliases</label><input name="search_aliases" value="{{ old('search_aliases') }}" placeholder="Optional aliases" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="mb-1 block text-xs font-semibold text-gray-600">Sort Order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                    <div class="flex items-center gap-2 pt-5"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') == '1') class="rounded border-gray-300"><span class="text-sm text-gray-700">Active</span></div>
                    <div class="sm:col-span-2 lg:col-span-3"><label class="mb-1 block text-xs font-semibold text-gray-600">Remarks</label><textarea name="remarks" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('remarks') }}</textarea></div>
                    <div class="sm:col-span-2 lg:col-span-3"><button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:#10212F">Save New Variant</button></div>
                </form>
            </details>
        @endif

        <div class="divide-y divide-gray-200">
            @forelse($materialType->variants->sortBy('sort_order') as $variant)
                <div class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900">{{ $variant->variant_name ?: 'Unnamed Variant' }} <span class="ml-2 text-xs font-normal text-gray-500">#{{ $variant->id }}{{ $variant->variant_code ? ' · '.$variant->variant_code : '' }}</span></div>
                            <p class="mt-1 text-xs text-gray-600">
                                {{ $variantSpecifications->firstWhere('id', $variant->material_specification_id)?->specification_name ?? 'No specification' }} ·
                                {{ $variantGrades->firstWhere('id', $variant->material_grade_id)?->grade_name ?? 'No grade' }} ·
                                {{ $variant->size_dimension ?: 'No size' }} · {{ $variant->finish ?: 'No finish' }} · {{ $variant->colour_shade ?: 'No colour' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $variant->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $variant->is_active ? 'Active' : 'Inactive' }}</span>
                            @if($canManageVariants)
                                <form method="POST" action="{{ route('material-types.variants.toggle-status', ['material_type' => $materialType->id, 'materialVariant' => $variant->id]) }}">@csrf @method('PATCH')<button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700">{{ $variant->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                            @endif
                        </div>
                    </div>
                    @if($canManageVariants)
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sm font-semibold text-sky-700">Edit variant attributes</summary>
                            <form method="POST" action="{{ route('material-types.variants.update', ['material_type' => $materialType->id, 'materialVariant' => $variant->id]) }}" class="mt-3 grid grid-cols-1 gap-3 rounded-lg bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-3">
                                @csrf @method('PUT')
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Variant Name *</label><input required name="variant_name" value="{{ $variant->variant_name }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Variant Code</label><input name="variant_code" value="{{ $variant->variant_code }}" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Specification</label><select name="material_specification_id" class="w-full rounded-lg border-gray-300 text-sm"><option value="">Not applicable</option>@foreach($variantSpecifications as $spec)<option value="{{ $spec->id }}" @selected((int)$variant->material_specification_id === (int)$spec->id)>{{ $spec->specification_name }}</option>@endforeach</select></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Grade</label><select name="material_grade_id" class="w-full rounded-lg border-gray-300 text-sm"><option value="">Not applicable</option>@foreach($variantGrades as $grade)<option value="{{ $grade->id }}" @selected((int)$variant->material_grade_id === (int)$grade->id)>{{ $grade->grade_name }}</option>@endforeach</select></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Unit</label><select name="unit_master_id" class="w-full rounded-lg border-gray-300 text-sm"><option value="">Use product default</option>@foreach($variantUnits as $unit)<option value="{{ $unit->id }}" @selected((int)$variant->unit_master_id === (int)$unit->id)>{{ $unit->unit_name }} ({{ $unit->unit_code }})</option>@endforeach</select></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Size / Dimension</label><input name="size_dimension" value="{{ $variant->size_dimension }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Finish</label><input name="finish" value="{{ $variant->finish }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Colour / Shade</label><input name="colour_shade" value="{{ $variant->colour_shade }}" maxlength="255" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Search Aliases</label><input name="search_aliases" value="{{ is_array($variant->search_aliases) ? implode(', ', $variant->search_aliases) : $variant->search_aliases }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-semibold text-gray-600">Sort Order</label><input type="number" min="0" name="sort_order" value="{{ $variant->sort_order ?? 0 }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div class="flex items-center gap-2 pt-5"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($variant->is_active) class="rounded border-gray-300"><span class="text-sm text-gray-700">Active</span></div>
                                <div class="sm:col-span-2 lg:col-span-3"><label class="mb-1 block text-xs font-semibold text-gray-600">Remarks</label><textarea name="remarks" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ $variant->remarks }}</textarea></div>
                                <div class="sm:col-span-2 lg:col-span-3"><button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:#10212F">Update Variant</button></div>
                            </form>
                        </details>
                    @endif
                </div>
            @empty
                <p class="p-6 text-center text-sm text-gray-500">No variants yet. Use Add Variant to create the first one.</p>
            @endforelse
        </div>
    </div>

    </details>

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