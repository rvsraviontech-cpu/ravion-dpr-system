@php
    $isEdit = isset($materialRequirement);

    $existingItems = $isEdit
        ? $materialRequirement->items->map(fn ($item) => [
            'id' => $item->id,
            'material_type_id' => $item->material_type_id,
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
      data-dependency-url-template="{{ route('materials.product-dependencies', ['materialType' => '__PRODUCT__']) }}">

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
                    Select a Product, then choose its Specification, Grade and Brand as applicable. Enter a custom specification when needed.
                </p>
            </div>

            <div id="entry-mode-badge"
                 class="hidden rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                Editing Item
            </div>
        </div>

        {{-- Four aligned fields per row on wide screens; no uneven column spans. --}}
        <div class="grid grid-cols-1 gap-x-4 gap-y-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="min-w-0">
                <x-rds.product-selector name="entry_product_id" label="Product" placeholder="Search Product..." />
            </div>
            <div class="min-w-0">
                <label class="{{ $labelClass }}" for="entry_specification_id">Specification / Size</label>
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
        <p class="mt-3 text-xs text-gray-500">Custom specification is optional. Unit defaults from the selected Product and can be changed.</p>

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

        <div class="overflow-x-auto">
            <table class="min-w-[1050px] w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-12 px-3 py-3 text-center">#</th>
                        <th class="min-w-[280px] px-3 py-3 text-left">Product</th>
                        <th class="min-w-[190px] px-3 py-3 text-left">Specification / Size</th>
                        <th class="min-w-[110px] px-3 py-3 text-left">Grade</th>
                        <th class="min-w-[150px] px-3 py-3 text-left">Brand</th>
                        <th class="w-24 px-3 py-3 text-right">Qty</th>
                        <th class="w-24 px-3 py-3 text-left">Unit</th>
                        <th class="min-w-[190px] px-3 py-3 text-left">Remarks</th>
                        <th class="w-32 px-3 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody id="requirement-items-body" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

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
    let availableBrands = [];
    let availableSpecifications = [];
    let availableGrades = [];
    let dependencyRequest = 0;

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

    function clearDependencies() {
        availableBrands = [];
        availableSpecifications = [];
        availableGrades = [];
        rebuildBrands();
        rebuildOptions(specificationSelect, [], 'Any / Not listed');
        rebuildOptions(gradeSelect, [], 'Any / Blank');
    }

    async function loadProductDependencies(productId, selectedBrandId = '', selectedBrandName = '', selectedSpecificationId = '', selectedSpecificationName = '', selectedGradeId = '', selectedGradeName = '') {
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
        selectedProduct = null;
        selectedUnitId = null;
        selectedUnitName = null;
        availableBrands = [];

        specificationInput.value = '';
        clearDependencies();
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
            selector.selectedProduct = item.product;
            selector.selectedId = String(item.material_type_id);
            selector.query = item.product_name;
            selector.results = [];
            selector.open = false;
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

            // Restore the transaction unit saved on this MR item.
            setEntryUnit(item.unit_master_id, item.unit_name || '');
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
        tableBody.innerHTML = '';

        items.forEach(function (item, index) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            row.innerHTML = `
                <td class="px-3 py-2.5 text-center">
                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-50 px-2 text-xs font-bold text-blue-700">
                        ${index + 1}
                    </span>
                </td>

                <td class="px-3 py-2.5">
                    <div class="font-semibold text-gray-800">
                        ${escapeHtml(item.product_name)}
                    </div>
                </td>

                <td class="px-3 py-2.5">
                    ${item.specification_name
                        ? `<div class="font-semibold text-gray-800">${escapeHtml(item.specification_name)}</div>`
                        : ''}
                    ${item.specification_text && (!item.specification_name || item.specification_text.trim().toLowerCase() !== item.specification_name.trim().toLowerCase())
                        ? `<div class="${item.specification_name ? 'mt-0.5 text-xs text-gray-500' : 'font-medium text-gray-800'}">${item.specification_name ? 'Additional details: ' : ''}${escapeHtml(item.specification_text)}</div>`
                        : ''}
                    ${!item.specification_name && !item.specification_text
                        ? '<span class="text-gray-400">-</span>'
                        : ''}
                </td>

                <td class="px-3 py-2.5">
                    ${item.grade_name ? escapeHtml(item.grade_name) : '<span class="text-gray-400">-</span>'}
                </td>

                <td class="px-3 py-2.5">
                    ${item.brand_name
                        ? `<span class="text-gray-800">${escapeHtml(item.brand_name)}</span>`
                        : '<span class="text-gray-400">-</span>'}
                </td>

                <td class="px-3 py-2.5 text-right font-semibold text-blue-700">
                    ${escapeHtml(item.required_quantity)}
                </td>

                <td class="px-3 py-2.5 font-medium text-gray-700">
                    ${item.unit_name
                        ? escapeHtml(item.unit_name)
                        : '<span class="text-gray-400">-</span>'}
                </td>

                <td class="px-3 py-2.5">
                    ${item.remarks
                        ? escapeHtml(item.remarks)
                        : '<span class="text-gray-400">-</span>'}
                </td>

                <td class="px-3 py-2.5">
                    <div class="flex justify-center gap-2">
                        <button type="button"
                                class="edit-item rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                data-index="${index}">
                            Edit
                        </button>

                        <button type="button"
                                class="remove-item rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                data-index="${index}">
                            Remove
                        </button>
                    </div>
                </td>
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
        selectedProduct = null;
        selectedUnitId = null;
        selectedUnitName = null;
        ++dependencyRequest;
        clearDependencies();
        specificationInput.value = '';
    });

    unitSelect?.addEventListener('change', function () {
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
});
</script>
