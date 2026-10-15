@php
    $isEdit = isset($materialRequirement);

    $existingItems = $isEdit
        ? $materialRequirement->items->map(fn ($item) => [
            'id' => $item->id,
            'material_type_id' => $item->material_type_id,
            'material_variant_id' => $item->material_variant_id,
            'variant_name' => $item->variant?->variant_name,
            'variant_size' => $item->variant?->size_dimension,
            'product_name' => $item->materialType?->material_type_name,
            'specification_text' => $item->specification_text,
            'material_specification_id' => $item->material_specification_id,
            'specification_name' => $item->specification?->specification_name,
            'material_grade_id' => $item->material_grade_id,
            'grade_name' => $item->grade?->grade_name,
            'brand_master_id' => $item->brand_master_id,
            'brand_name' => $item->brand?->brand_name,
            'required_quantity' => $item->required_quantity,
            'unit_master_id' => $item->unit_master_id,
            'unit_name' => $item->unit?->unit_name,
            'remarks' => $item->remarks,
        ])->values()->all()
        : [];

    $formItems = old('items', $existingItems);

    $inputClass = 'block h-11 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';
@endphp

@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
        <p class="mb-2 font-semibold">Please correct the following:</p>

        <ul class="ml-5 list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $isEdit
          ? route('material-requirements.update', $materialRequirement)
          : route('material-requirements.store') }}"
      id="material-requirement-form"
      data-dependency-url-template="{{ route('materials.product-dependencies', ['materialType' => '__PRODUCT__']) }}"
       data-bible-catalogue-url="{{ route('materials.bible-catalogue') }}">

    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-gray-800">Requirement Information</h2>
            <p class="mt-1 text-xs text-gray-500">Project, required date and priority for this requirement.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="{{ $labelClass }}">Project <span class="text-red-500">*</span></label>

                <select name="project_id" id="project_id" class="{{ $inputClass }}" required>
                    <option value="">Select Project</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ (string) old('project_id', $isEdit ? $materialRequirement->project_id : '') === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Project Block</label>

                <select name="project_block_id" id="project_block_id" class="{{ $inputClass }}">
                    <option value="">Select Block</option>

                    @foreach($projectBlocks as $block)
                        <option value="{{ $block->id }}"
                                data-project="{{ $block->project_id }}"
                            {{ (string) old('project_block_id', $isEdit ? $materialRequirement->project_block_id : '') === (string) $block->id ? 'selected' : '' }}>
                            {{ $block->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">Required Date</label>

                <input type="date"
                       name="required_date"
                       value="{{ old('required_date', $isEdit ? $materialRequirement->required_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label class="{{ $labelClass }}">Priority <span class="text-red-500">*</span></label>

                <select name="priority" class="{{ $inputClass }}" required>
                    @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                        <option value="{{ $priority }}"
                            {{ old('priority', $isEdit ? $materialRequirement->priority : 'Normal') === $priority ? 'selected' : '' }}>
                            {{ $priority }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label class="{{ $labelClass }}">General Remarks</label>

                <textarea name="remarks"
                          rows="2"
                          class="{{ $inputClass }}"
                          placeholder="Optional notes">{{ old('remarks', $isEdit ? $materialRequirement->remarks : '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Add Product to Requirement</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Browse Trade → Main Category → Sub Category → Item, or search directly for a Product. Then choose Bible Sub Item and Size / Specification.
                </p>
            </div>

            <div id="entry-mode-badge"
                 class="hidden rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                Editing Item
            </div>
        </div>

        {{-- Category-first browsing and direct Product search are complementary selection methods. --}}
        <div class="grid grid-cols-1 gap-x-4 gap-y-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="min-w-0">
                <x-rds.product-selector name="entry_product_id" label="Direct Product Search (Alternative)" placeholder="Search any Product..." />
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_trade">Trade Category</label>
                <select id="entry_trade" class="{{ $inputClass }}"><option value="">Select Trade Category</option></select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_main">Main Category</label>
                <select id="entry_main" class="{{ $inputClass }}"><option value="">Select Trade first</option></select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_sub">Sub Category</label>
                <select id="entry_sub" class="{{ $inputClass }}"><option value="">Select Main Category first</option></select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_browse_product">Item / Product (Bible E)</label>
                <select id="entry_browse_product" class="{{ $inputClass }}"><option value="">Select Sub Category first</option></select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_sub_item">Sub Item / Variant (Bible F)</label>
                <select id="entry_sub_item" class="{{ $inputClass }}"><option value="">Select Product first</option></select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_variant">Size / Specification (Bible G)</label>
                <select id="entry_variant" class="{{ $inputClass }}"><option value="">Select Sub Item first</option></select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_specification_id">Additional Master Specification</label>
                <select id="entry_specification_id" class="{{ $inputClass }}">
                    <option value="">Any / Not listed</option>
                </select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_grade">Grade</label>
                <select id="entry_grade" class="{{ $inputClass }}">
                    <option value="">Any / Blank</option>
                </select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_brand">Brand</label>
                <select id="entry_brand" class="{{ $inputClass }}">
                    <option value="">Any / Blank</option>
                </select>
                <p id="entry-brand-hint" class="mt-1 text-xs text-amber-700" hidden></p>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_specification">Custom specification / size</label>
                <input type="text" id="entry_specification" maxlength="500" class="{{ $inputClass }}"
                       placeholder='If not listed: 120 mm, 6", SN4'>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_quantity">Quantity <span class="text-red-500">*</span></label>
                <input type="number" id="entry_quantity" min="0.001" step="0.001"
                       class="{{ $inputClass }} text-right" placeholder="0">
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_unit">Unit <span class="text-red-500">*</span></label>
                <select id="entry_unit" class="{{ $inputClass }}">
                    <option value="">Select Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->unit_code ?: $unit->symbol ?: $unit->unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_remarks">Remarks</label>
                <input type="text" id="entry_remarks" class="{{ $inputClass }}" placeholder="Optional">
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500">Bible Sub Item may be blank (e.g. cement). Unit defaults from the selected Bible variant, but engineers may select any Unit Master unit (Nos, Trips, Tons, CFT, etc.). No automatic unit conversion is applied. Legacy products retain their existing selection workflow.</p>

        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <button type="button"
                    id="cancel-item-edit"
                    class="hidden rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel Edit
            </button>

            <button type="button"
                    id="add-item-to-list"
                    class="rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                + Add to Requirement
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Requirement Items</h2>
                <p class="mt-1 text-xs text-gray-500">Final requirement sheet to be submitted for approval.</p>
            </div>

            <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                <span id="item-count">0</span> Item(s)
            </div>
        </div>

        {{-- Compact, responsive material list; original item IDs and form inputs are unchanged. --}}
        <div id="requirement-items-body" class="divide-y divide-gray-100"></div>

        <div id="empty-requirement-state"
             class="px-6 py-10 text-center text-sm text-gray-500">
            No Products added yet. Use the Product entry area above.
        </div>
    </div>

    <div id="hidden-item-inputs"></div>

    <div class="mt-5 flex justify-end gap-3">
        <a href="{{ $isEdit
                ? route('material-requirements.show', $materialRequirement)
                : route('material-requirements.index') }}"
           class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Cancel
        </a>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update Draft' : 'Save Draft' }}
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('material-requirement-form');
    const productHidden = form.querySelector('input[name="entry_product_id"]');
    const tradeSelect = document.getElementById('entry_trade');
    const mainSelect = document.getElementById('entry_main');
    const subSelect = document.getElementById('entry_sub');
    const browseProductSelect = document.getElementById('entry_browse_product');
    const catalogueUrl = form.dataset.bibleCatalogueUrl;
    const subItemSelect = document.getElementById('entry_sub_item');
    const variantSelect = document.getElementById('entry_variant');
    const specificationInput = document.getElementById('entry_specification');
    const specificationSelect = document.getElementById('entry_specification_id');
    const gradeSelect = document.getElementById('entry_grade');
    const brandSelect = document.getElementById('entry_brand');
    const brandHint = document.getElementById('entry-brand-hint');
    const quantityInput = document.getElementById('entry_quantity');
    const unitSelect = document.getElementById('entry_unit');
    const remarksInput = document.getElementById('entry_remarks');
    const addButton = document.getElementById('add-item-to-list');
    const cancelEditButton = document.getElementById('cancel-item-edit');
    const editBadge = document.getElementById('entry-mode-badge');
    const tableBody = document.getElementById('requirement-items-body');
    const hiddenInputs = document.getElementById('hidden-item-inputs');
    const emptyState = document.getElementById('empty-requirement-state');
    const itemCount = document.getElementById('item-count');
    const dependencyTemplate = form.dataset.dependencyUrlTemplate;

    const initialItems = @json($formItems);

    let items = [];
    let editingIndex = null;
    let selectedProduct = null;
    let selectedUnitId = null;
    let selectedUnitName = null;
    let unitManuallySelected = false;
    let availableBrands = [];
    let availableSpecifications = [];
    let availableGrades = [];
    let dependencyRequest = 0;
    let bibleVariants = [];
    let bibleMappings = [];
    let activeDependencyData = null;
    let entryVariantId = '';
    let catalogueRequest = 0;
    let suppressProductCleared = false;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function findProductSelectorRoot() {
        return productHidden?.closest('[x-data]');
    }

    function getProductSelectorData() {
        const root = findProductSelectorRoot();

        if (! root || ! window.Alpine) {
            return null;
        }

        try {
            return Alpine.$data(root);
        } catch (error) {
            return null;
        }
    }

    function setEntryUnit(unitId = '', unitName = '') {
        selectedUnitId = unitId ? Number(unitId) : null;
        selectedUnitName = unitName || '';

        if (! unitSelect) return;

        unitSelect.value = selectedUnitId ? String(selectedUnitId) : '';

        if (selectedUnitId && ! unitSelect.value) {
            unitSelect.add(new Option(
                selectedUnitName || `Unit #${selectedUnitId}`,
                selectedUnitId,
                true,
                true
            ));
        }
    }

    function rebuildBrands(selectedId = '', selectedName = '') {
        brandSelect.innerHTML = '';
        brandSelect.add(new Option('Any / Blank', ''));
        if (brandHint) {
            brandHint.hidden = availableBrands.length > 0 || ! productHidden?.value;
            brandHint.textContent = brandHint.hidden ? '' : 'No approved brands mapped to this Product. Leave blank if not applicable.';
        }

        let selectedFound = false;

        availableBrands.forEach(function (brand) {
            const isSelected = String(brand.id) === String(selectedId);

            brandSelect.add(
                new Option(
                    brand.name,
                    brand.id,
                    isSelected,
                    isSelected
                )
            );

            if (isSelected) {
                selectedFound = true;
            }
        });

        if (selectedId && ! selectedFound) {
            brandSelect.add(
                new Option(
                    selectedName || 'Existing Brand',
                    selectedId,
                    true,
                    true
                )
            );
        }
    }

    function rebuildOptions(select, options, placeholder, selectedId = '', selectedName = '') {
        select.replaceChildren(new Option(placeholder, ''));
        let found = false;
        options.forEach(function (option) {
            const selected = String(option.id) === String(selectedId) && String(selectedId) !== '';
            select.add(new Option(option.name, option.id, selected, selected));
            if (selected) found = true;
        });
        // Existing historical selections remain editable without changing their saved ID.
        if (selectedId && ! found) {
            select.add(new Option(selectedName || 'Existing selection', selectedId, true, true));
        }
    }

    function uniqueOptions(rows, getId, getName) {
        const seen = new Set();
        return rows.map(row => ({ id: String(getId(row) ?? ''), name: String(getName(row) ?? '') }))
            .filter(row => row.id && !seen.has(row.id) && seen.add(row.id));
    }

    async function catalogue(level, params = {}) {
        const url = new URL(catalogueUrl, window.location.href);
        url.searchParams.set('level', level);
        Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
        const response = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) throw new Error(`Cannot load Material Bible ${level} (${response.status}).`);
        const payload = await response.json();
        return Array.isArray(payload.data) ? payload.data : [];
    }

    function resetBrowseChildren(level) {
        if (level <= 1) rebuildOptions(mainSelect, [], 'Select Main Category');
        if (level <= 2) rebuildOptions(subSelect, [], 'Select Sub Category');
        if (level <= 3) rebuildOptions(browseProductSelect, [], 'Select Item / Product');
    }

    function reportCatalogueError(error) {
        console.error(error);
        alert('Unable to load Material Bible categories. Please refresh and try again.');
    }

    async function loadTrades(preferred = '') {
        const token = ++catalogueRequest;
        try {
            const rows = await catalogue('trades');
            if (token !== catalogueRequest) return;
            rebuildOptions(tradeSelect, rows, 'Select Trade Category', preferred);
        } catch (error) { if (token === catalogueRequest) reportCatalogueError(error); }
    }

    async function loadMains(preferred = '') {
        const token = ++catalogueRequest;
        resetBrowseChildren(1);
        if (!tradeSelect.value) return;
        try {
            const rows = await catalogue('main_categories', { trade_id: tradeSelect.value });
            if (token !== catalogueRequest) return;
            rebuildOptions(mainSelect, rows, 'Select Main Category', preferred);
        } catch (error) { if (token === catalogueRequest) reportCatalogueError(error); }
    }

    async function loadSubs(preferred = '') {
        const token = ++catalogueRequest;
        resetBrowseChildren(2);
        if (!mainSelect.value) return;
        try {
            const rows = await catalogue('sub_categories', { main_category_id: mainSelect.value });
            if (token !== catalogueRequest) return;
            rebuildOptions(subSelect, rows, 'Select Sub Category', preferred);
        } catch (error) { if (token === catalogueRequest) reportCatalogueError(error); }
    }

    async function loadBrowseProducts(preferred = '') {
        const token = ++catalogueRequest;
        resetBrowseChildren(3);
        if (!subSelect.value) return;
        try {
            const rows = await catalogue('products', { sub_category_id: subSelect.value });
            if (token !== catalogueRequest) return;
            rebuildOptions(browseProductSelect, rows, 'Select Item / Product', preferred);
        } catch (error) { if (token === catalogueRequest) reportCatalogueError(error); }
    }

    async function syncCategoryPath(preferred = {}) {
        const mapping = bibleMappings.find(m =>
            (!preferred.variant || String(m.material_variant_id) === String(preferred.variant))
        ) || bibleMappings[0];
        if (!mapping?.trade?.id) return;
        await loadTrades(String(mapping.trade.id));
        await loadMains(String(mapping.main_category?.id || ''));
        await loadSubs(String(mapping.sub_category?.id || ''));
        await loadBrowseProducts(String(productHidden?.value || ''));
    }

    function selectProductInSearch(product) {
        const selector = getProductSelectorData();
        if (selector) {
            suppressProductCleared = true;
            selector.selectedProduct = product;
            selector.selectedId = String(product.id);
            selector.query = product.name;
            selector.results = [];
            selector.open = false;
            suppressProductCleared = false;
        }
        if (productHidden) productHidden.value = String(product.id);
    }

    function clearProductForCategoryChange() {
        ++dependencyRequest;
        selectedProduct = null;
        selectedUnitId = null;
        selectedUnitName = null;
        unitManuallySelected = false;
        clearDependencies();
        const selector = getProductSelectorData();
        suppressProductCleared = true;
        if (selector && typeof selector.clearProduct === 'function') selector.clearProduct();
        else if (productHidden) productHidden.value = '';
        suppressProductCleared = false;
        if (productHidden) productHidden.value = '';
        if (unitSelect) unitSelect.value = '';
    }

    tradeSelect.addEventListener('change', async () => {
        clearProductForCategoryChange();
        await loadMains();
    });
    mainSelect.addEventListener('change', async () => {
        clearProductForCategoryChange();
        await loadSubs();
    });
    subSelect.addEventListener('change', async () => {
        clearProductForCategoryChange();
        await loadBrowseProducts();
    });
    browseProductSelect.addEventListener('change', async () => {
        const id = browseProductSelect.value;
        if (!id) { clearProductForCategoryChange(); return; }
        const product = { id: Number(id), name: browseProductSelect.selectedOptions[0]?.text || `Product #${id}` };
        selectedProduct = product;
        selectProductInSearch(product);
        unitManuallySelected = false;
        specificationInput.value = '';
        try { await loadProductDependencies(id, '', '', '', '', '', '', true); }
        catch (error) { console.error(error); alert('Unable to load selected Product details.'); }
    });

    function eligibleVariants() {
        const ids = bibleMappings.filter(m => String(m.trade?.id) === tradeSelect.value && String(m.main_category?.id) === mainSelect.value && String(m.sub_category?.id) === subSelect.value)
            .map(m => String(m.material_variant_id || ''));
        if (!bibleMappings.length) return bibleVariants;
        return bibleVariants.filter(v => ids.includes(String(v.id)));
    }

    function refreshSubItems(preferred = null) {
        const variants = eligibleVariants();
        const names = [...new Set(variants.map(v => String(v.sub_item || '')))];
        subItemSelect.replaceChildren();
        if (!variants.length) {
            subItemSelect.add(new Option('Not applicable', ''));
            variantSelect.replaceChildren(new Option('Not applicable', ''));
            entryVariantId = '';
            return;
        }
        names.forEach(name => subItemSelect.add(new Option(name || '(No Sub Item)', name)));
        if (preferred !== null && names.includes(preferred)) subItemSelect.value = preferred;
        refreshVariantSizes();
    }

    function refreshVariantSizes(preferredId = '') {
        const variants = eligibleVariants().filter(v => String(v.sub_item || '') === subItemSelect.value);
        variantSelect.replaceChildren(new Option('Select Size / Specification', ''));
        variants.forEach(v => variantSelect.add(new Option(v.size_specification || v.name, String(v.id))));
        if (preferredId && variants.some(v => String(v.id) === String(preferredId))) variantSelect.value = String(preferredId);
        entryVariantId = variantSelect.value;
        applyVariantSelection();
    }

    function applyVariantSelection() {
        entryVariantId = variantSelect.value;
        const variant = bibleVariants.find(v => String(v.id) === String(entryVariantId));
        if (!unitManuallySelected) {
            if (variant?.unit?.id) setEntryUnit(variant.unit.id, variant.unit.code || variant.unit.symbol || variant.unit.name);
            else if (activeDependencyData?.unit) setEntryUnit(activeDependencyData.unit.id, activeDependencyData.unit.code || activeDependencyData.unit.symbol || activeDependencyData.unit.name);
        }
        const variantBrands = variant?.brands || [];
        if (variantBrands.length) availableBrands = variantBrands;
        else availableBrands = activeDependencyData?.brands || [];
        rebuildBrands();
    }

    subItemSelect.addEventListener('change', () => refreshVariantSizes());
    variantSelect.addEventListener('change', applyVariantSelection);

    function clearDependencies() {
        availableBrands = [];
        availableSpecifications = [];
        availableGrades = [];
        bibleVariants = [];
        bibleMappings = [];
        activeDependencyData = null;
        entryVariantId = '';
        [subItemSelect, variantSelect].forEach(select => select.replaceChildren(new Option('Select Product first', '')));
        rebuildBrands();
        rebuildOptions(specificationSelect, [], 'Any / Not listed');
        rebuildOptions(gradeSelect, [], 'Any / Blank');
    }

    async function loadProductDependencies(productId, selectedBrandId = '', selectedBrandName = '', selectedSpecificationId = '', selectedSpecificationName = '', selectedGradeId = '', selectedGradeName = '', preserveCategoryPath = false) {
        const requestId = ++dependencyRequest;
        selectedUnitId = null;
        selectedUnitName = null;
        clearDependencies();

        if (! productId) {
            return null;
        }

        const response = await fetch(
            dependencyTemplate.replace('__PRODUCT__', encodeURIComponent(productId)),
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        if (! response.ok) {
            throw new Error('Unable to load Product details.');
        }

        const payload = await response.json();
        const data = payload.data || {};
        if (requestId !== dependencyRequest) return null;

        setEntryUnit(
            data.unit?.id || '',
            data.unit?.code || data.unit?.symbol || data.unit?.name || ''
        );
        activeDependencyData = data;
        bibleVariants = Array.isArray(data.variants) ? data.variants : [];
        bibleMappings = Array.isArray(data.bible_mappings) ? data.bible_mappings : [];
        if (!preserveCategoryPath) await syncCategoryPath();
        if (requestId !== dependencyRequest) return null;
        refreshSubItems();
        availableBrands = Array.isArray(data.brands) ? data.brands : [];

        availableSpecifications = Array.isArray(data.specifications) ? data.specifications : [];
        availableGrades = Array.isArray(data.grades) ? data.grades : [];
        rebuildBrands(selectedBrandId, selectedBrandName);
        rebuildOptions(specificationSelect, availableSpecifications, 'Any / Not listed', selectedSpecificationId, selectedSpecificationName);
        rebuildOptions(gradeSelect, availableGrades, 'Any / Blank', selectedGradeId, selectedGradeName);

        return data;
    }

    function clearEntry() {
        ++dependencyRequest;
        editingIndex = null;
        unitManuallySelected = false;
        selectedProduct = null;
        selectedUnitId = null;
        selectedUnitName = null;
        availableBrands = [];

        specificationInput.value = '';
        clearDependencies();
        ++catalogueRequest;
        resetBrowseChildren(1);
        tradeSelect.value = '';
        loadTrades();
        quantityInput.value = '';
        if (unitSelect) unitSelect.value = '';
        remarksInput.value = '';
        rebuildBrands();

        const selector = getProductSelectorData();

        if (selector) {
            selector.clearProduct();
        } else if (productHidden) {
            productHidden.value = '';
        }

        editBadge.classList.add('hidden');
        cancelEditButton.classList.add('hidden');
        addButton.textContent = '+ Add to Requirement';
    }

    async function setEntryForEdit(index) {
        const item = items[index];

        if (! item) {
            return;
        }

        editingIndex = index;
        unitManuallySelected = false;
        selectedProduct = item.product;
        selectedUnitId = item.unit_master_id;
        selectedUnitName = item.unit_name;

        specificationInput.value = item.specification_text && (!item.specification_name || item.specification_text.trim().toLowerCase() !== item.specification_name.trim().toLowerCase()) ? item.specification_text : '';
        rebuildOptions(specificationSelect, [], 'Any / Not listed', item.material_specification_id || '', item.specification_name || '');
        rebuildOptions(gradeSelect, [], 'Any / Blank', item.material_grade_id || '', item.grade_name || '');
        quantityInput.value = item.required_quantity || '';
        remarksInput.value = item.remarks || '';

        const selector = getProductSelectorData();

        if (selector) {
            selectProductInSearch(item.product);
        }

        try {
            await loadProductDependencies(
                item.material_type_id,
                item.brand_master_id || '',
                item.brand_name || '',
                item.material_specification_id || '',
                item.specification_name || '',
                item.material_grade_id || '',
                item.grade_name || ''
            );

            if (item.material_variant_id) {
                const variant = bibleVariants.find(v => String(v.id) === String(item.material_variant_id));
                if (variant) {
                    const mapping = bibleMappings.find(m => String(m.material_variant_id) === String(variant.id));
                    if (mapping) await syncCategoryPath({ variant: String(variant.id) });
                    refreshSubItems(String(variant.sub_item || ''));
                    refreshVariantSizes(String(variant.id));
                    rebuildBrands(item.brand_master_id || '', item.brand_name || '');
                } else {
                    // Preserve inactive/historical variant IDs on an unchanged draft item.
                    variantSelect.add(new Option(item.variant_name || 'Existing variant', String(item.material_variant_id), true, true));
                    entryVariantId = String(item.material_variant_id);
                }
            }
            // Restore the transaction unit saved on this MR item.
            setEntryUnit(item.unit_master_id, item.unit_name || '');
            unitManuallySelected = true;
        } catch (error) {
            console.error(error);
            rebuildBrands(item.brand_master_id || '', item.brand_name || '');
            rebuildOptions(specificationSelect, [], 'Any / Not listed', item.material_specification_id || '', item.specification_name || '');
            rebuildOptions(gradeSelect, [], 'Any / Blank', item.material_grade_id || '', item.grade_name || '');
        }

        editBadge.classList.remove('hidden');
        cancelEditButton.classList.remove('hidden');
        addButton.textContent = 'Update Item';

        document.getElementById('entry_specification')
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function buildHiddenInputs() {
        hiddenInputs.innerHTML = '';

        items.forEach(function (item, index) {
            const fields = {
                id: item.id || '',
                material_type_id: item.material_type_id,
                material_variant_id: item.material_variant_id || '',
                material_specification_id: item.material_specification_id || '',
                material_grade_id: item.material_grade_id || '',
                specification_text: item.specification_text || '',
                brand_master_id: item.brand_master_id || '',
                required_quantity: item.required_quantity,
                unit_master_id: item.unit_master_id,
                remarks: item.remarks || '',
            };

            Object.entries(fields).forEach(function ([key, value]) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = value ?? '';
                hiddenInputs.appendChild(input);
            });
        });
    }

    function renderItems() {
        tableBody.replaceChildren();

        items.forEach(function (item, index) {
            const row = document.createElement('div');
            row.className = 'flex items-start gap-3 px-4 py-3 transition-colors hover:bg-gray-50 sm:items-center sm:px-5';

            // Bible variant already includes its sub-item and size; avoid duplicating them.
            const detail = [];
            if (item.variant_name) detail.push(item.variant_name);
            if (item.specification_name && item.specification_name !== item.variant_name) detail.push(item.specification_name);
            if (item.specification_text &&
                (!item.specification_name || item.specification_text.trim().toLowerCase() !== item.specification_name.trim().toLowerCase()) &&
                item.specification_text.trim().toLowerCase() !== String(item.variant_name || '').trim().toLowerCase()) {
                detail.push(item.specification_text);
            }
            const secondary = [];
            if (item.grade_name) secondary.push(`Grade: ${item.grade_name}`);
            if (item.brand_name) secondary.push(`Brand: ${item.brand_name}`);
            if (item.remarks) secondary.push(`Remarks: ${item.remarks}`);

            row.innerHTML = `
                <span class="mt-0.5 shrink-0 text-sm font-semibold tabular-nums text-gray-500 sm:mt-0">${index + 1}.</span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 text-sm leading-6">
                        <span class="font-semibold text-gray-900">${escapeHtml(item.product_name)}</span>
                        ${detail.length ? `<span class="text-gray-600">${detail.map(escapeHtml).join(' · ')}</span>` : ''}
                        <span class="whitespace-nowrap font-bold tabular-nums text-blue-700">${escapeHtml(item.required_quantity)} ${escapeHtml(item.unit_name || '')}</span>
                    </div>
                    ${secondary.length ? `<div class="mt-0.5 break-words text-xs leading-5 text-gray-500">${secondary.map(escapeHtml).join('  ·  ')}</div>` : ''}
                </div>
                <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                    <button type="button" class="edit-item inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300" data-index="${index}" title="Edit material" aria-label="Edit material ${index + 1}">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/></svg>
                    </button>
                    <button type="button" class="remove-item inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-red-300 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-300" data-index="${index}" title="Remove material" aria-label="Remove material ${index + 1}">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 10v6M14 10v6"/></svg>
                    </button>
                </div>
            `;

            tableBody.appendChild(row);
        });

        itemCount.textContent = items.length;
        emptyState.classList.toggle('hidden', items.length > 0);
        buildHiddenInputs();
    }

    function normalizeInitialItems() {
        items = (initialItems || [])
            .filter(item => item.material_type_id)
            .map(function (item) {
                const productId = Number(item.material_type_id);

                return {
                    id: item.id || '',
                    material_type_id: productId,
                    material_variant_id: item.material_variant_id || '',
                    variant_name: item.variant_name || '',
                    variant_size: item.variant_size || '',
                    product_name: item.product_name || `Product #${productId}`,
                    product: {
                        id: productId,
                        name: item.product_name || `Product #${productId}`,
                        unit: item.unit_master_id ? {
                            id: Number(item.unit_master_id),
                            name: item.unit_name || '',
                            code: '',
                            symbol: '',
                        } : null,
                        product_group: null,
                        product_type: null,
                    },
                    specification_text: item.specification_text || '',
                    material_specification_id: item.material_specification_id || '',
                    specification_name: item.specification_name || '',
                    material_grade_id: item.material_grade_id || '',
                    grade_name: item.grade_name || '',
                    brand_master_id: item.brand_master_id || '',
                    brand_name: item.brand_name || '',
                    required_quantity: item.required_quantity || '',
                    unit_master_id: item.unit_master_id || '',
                    unit_name: item.unit_name || '',
                    remarks: item.remarks || '',
                };
            });

        renderItems();
    }

    form.addEventListener('ravion-product-selected', async function (event) {
        const product = event.detail?.product;

        if (! product) {
            return;
        }

        selectedProduct = product;
        unitManuallySelected = false;
        specificationInput.value = '';

        try {
            const data = await loadProductDependencies(product.id);

            if (data?.unit) {
                selectedProduct.unit = data.unit;
            }
        } catch (error) {
            console.error(error);
            alert('Unable to load Product details. Please select the Product again.');
        }
    });

    form.addEventListener('ravion-product-cleared', function () {
        if (suppressProductCleared) return;
        selectedProduct = null;
        selectedUnitId = null;
        selectedUnitName = null;
        unitManuallySelected = false;
        ++dependencyRequest;
        clearDependencies();
        ++catalogueRequest;
        resetBrowseChildren(1);
        tradeSelect.value = '';
        loadTrades();
        specificationInput.value = '';
    });

    unitSelect?.addEventListener('change', function () {
        // Requirement/ordering units are independent of the Bible's default stock unit.
        // Preserve the engineer's choice even if Sub Item or Size is changed later.
        unitManuallySelected = Boolean(this.value);
        selectedUnitId = this.value ? Number(this.value) : null;
        selectedUnitName = this.options[this.selectedIndex]?.text?.trim() || '';
    });

    addButton.addEventListener('click', async function () {
        const productId = Number(productHidden?.value || 0);
        const quantity = Number(quantityInput.value || 0);

        if (! productId) {
            alert('Select a Product first.');
            return;
        }

        if (! quantity || quantity <= 0) {
            alert('Enter a quantity greater than zero.');
            quantityInput.focus();
            return;
        }

        if (! unitSelect?.value) {
            alert('Select a Unit for this requirement item.');
            unitSelect?.focus();
            return;
        }

        if (bibleVariants.length && !variantSelect.value) {
            alert('Select Size / Specification for the selected Bible Product.');
            variantSelect.focus();
            return;
        }
        selectedUnitId = Number(unitSelect.value);
        selectedUnitName = unitSelect.options[unitSelect.selectedIndex]?.text?.trim() || '';

        const selector = getProductSelectorData();
        const productFromSelector = selector?.selectedProduct || selectedProduct;

        const selectedBrandOption = brandSelect.options[brandSelect.selectedIndex];
        const selectedSpecificationOption = specificationSelect.options[specificationSelect.selectedIndex];
        const selectedGradeOption = gradeSelect.options[gradeSelect.selectedIndex];

        const item = {
            id: editingIndex !== null ? (items[editingIndex]?.id || '') : '',
            material_type_id: productId,
            material_variant_id: variantSelect.value || '',
            variant_name: bibleVariants.find(v => String(v.id) === variantSelect.value)?.name || (editingIndex !== null ? items[editingIndex]?.variant_name : '') || '',
            variant_size: bibleVariants.find(v => String(v.id) === variantSelect.value)?.size_specification || '',
            product_name: productFromSelector?.name || selectedProduct?.name || `Product #${productId}`,
            product: productFromSelector || selectedProduct || {
                id: productId,
                name: `Product #${productId}`,
                unit: selectedUnitId ? {
                    id: selectedUnitId,
                    name: selectedUnitName || '',
                } : null,
            },
            specification_text: specificationInput.value.trim() || (specificationSelect.value ? (selectedSpecificationOption?.text || '') : ''),
            material_specification_id: specificationSelect.value || '',
            specification_name: specificationSelect.value ? (selectedSpecificationOption?.text || '') : '',
            material_grade_id: gradeSelect.value || '',
            grade_name: gradeSelect.value ? (selectedGradeOption?.text || '') : '',
            brand_master_id: brandSelect.value || '',
            brand_name: brandSelect.value ? (selectedBrandOption?.text || '') : '',
            required_quantity: quantityInput.value,
            unit_master_id: selectedUnitId,
            unit_name: selectedUnitName || '',
            remarks: remarksInput.value.trim(),
        };

        if (editingIndex !== null) {
            items[editingIndex] = item;
        } else {
            items.push(item);
        }

        renderItems();
        clearEntry();
    });

    cancelEditButton.addEventListener('click', clearEntry);

    tableBody.addEventListener('click', function (event) {
        const editButton = event.target.closest('.edit-item');
        const removeButton = event.target.closest('.remove-item');

        if (editButton) {
            setEntryForEdit(Number(editButton.dataset.index));
            return;
        }

        if (removeButton) {
            const index = Number(removeButton.dataset.index);

            if (! confirm('Remove this Product from the requirement?')) {
                return;
            }

            items.splice(index, 1);

            if (editingIndex === index) {
                clearEntry();
            } else if (editingIndex !== null && editingIndex > index) {
                editingIndex--;
            }

            renderItems();
        }
    });

    form.addEventListener('submit', function (event) {
        if (editingIndex !== null) {
            event.preventDefault();
            alert('Finish updating the item before saving the requirement.');
            return;
        }
        if (items.length === 0) {
            event.preventDefault();
            alert('Add at least one Product to the requirement before saving.');
        }
    });

    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');

    if (projectSelect && blockSelect) {
        const originalOptions = Array.from(blockSelect.options)
            .filter(option => option.value !== '')
            .map(option => option.cloneNode(true));

        function filterBlocks(preserve = true) {
            const current = preserve ? blockSelect.value : '';

            blockSelect.innerHTML = '';
            blockSelect.add(new Option('Select Block', ''));

            originalOptions.forEach(function (option) {
                if (
                    ! projectSelect.value
                    || String(option.dataset.project) === String(projectSelect.value)
                ) {
                    const clone = option.cloneNode(true);
                    clone.selected = String(clone.value) === String(current);
                    blockSelect.add(clone);
                }
            });
        }

        projectSelect.addEventListener('change', function () {
            filterBlocks(false);
        });

        filterBlocks(true);
    }

    normalizeInitialItems();
    loadTrades();
});
</script>
