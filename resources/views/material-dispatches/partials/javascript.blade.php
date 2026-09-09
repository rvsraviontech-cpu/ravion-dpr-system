<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('material-dispatch-form');
    if (!form) return;

    const projectSelect = document.getElementById('project_id');
    const deliveryAddress = document.getElementById('delivery_address');
    const requirementsButton = document.getElementById('refresh-requirements');
    const requirementsMessage = document.getElementById('requirements-message');
    const requirementsList = document.getElementById('requirements-list');

    const productHidden = form.querySelector('input[name="entry_product_id"]');
    const specificationInput = document.getElementById('entry_specification');
    const brandSelect = document.getElementById('entry_brand');
    const quantityInput = document.getElementById('entry_quantity');
    const unitSelect = document.getElementById('entry_unit');
    const remarksInput = document.getElementById('entry_remarks');
    const addDirectButton = document.getElementById('add-direct-item');
    const cancelEditButton = document.getElementById('cancel-item-edit');
    const editBadge = document.getElementById('entry-mode-badge');

    const tableBody = document.getElementById('dispatch-items-body');
    const hiddenInputs = document.getElementById('hidden-item-inputs');
    const emptyState = document.getElementById('empty-dispatch-state');
    const itemCount = document.getElementById('dispatch-item-count');
    const stickyItemCount = document.getElementById('sticky-item-count');

    const requirementsUrl = form.dataset.requirementsUrl;
    const dependencyTemplate = form.dataset.dependencyUrlTemplate;
    const initialItems = @json($formItems ?? []);

    let items = [];
    let editingIndex = null;
    let selectedProduct = null;
    let availableBrands = [];

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function formatQty(value) {
        const numeric = Number(value || 0);
        if (!Number.isFinite(numeric)) return '0';

        return numeric.toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
        });
    }

    function findProductSelectorRoot() {
        return productHidden?.closest('[x-data]');
    }

    function getProductSelectorData() {
        const root = findProductSelectorRoot();
        if (!root || !window.Alpine) return null;

        try {
            return Alpine.$data(root);
        } catch (error) {
            return null;
        }
    }

    function getUnitName(unitId) {
        const option = Array.from(unitSelect.options)
            .find(option => String(option.value) === String(unitId));

        return option ? option.textContent.trim() : '';
    }

    function rebuildBrands(selectedId = '', selectedName = '') {
        brandSelect.innerHTML = '';
        brandSelect.add(new Option('Any / Blank', ''));

        let found = false;

        availableBrands.forEach(function (brand) {
            const isSelected = String(brand.id) === String(selectedId);

            brandSelect.add(
                new Option(brand.name, brand.id, isSelected, isSelected)
            );

            if (isSelected) found = true;
        });

        if (selectedId && !found) {
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

    async function loadProductDependencies(
        productId,
        selectedBrandId = '',
        selectedBrandName = '',
        selectedUnitId = ''
    ) {
        availableBrands = [];

        if (!productId) {
            rebuildBrands();
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

        if (!response.ok) {
            throw new Error('Unable to load Product details.');
        }

        const payload = await response.json();
        const data = payload.data || {};

        availableBrands = Array.isArray(data.brands) ? data.brands : [];
        rebuildBrands(selectedBrandId, selectedBrandName);

        const unitId = selectedUnitId || data.unit?.id || '';
        if (unitId) unitSelect.value = String(unitId);

        return data;
    }

    function clearEntry() {
        editingIndex = null;
        selectedProduct = null;
        availableBrands = [];

        specificationInput.value = '';
        quantityInput.value = '';
        remarksInput.value = '';
        unitSelect.value = '';
        rebuildBrands();

        const selector = getProductSelectorData();

        if (selector) {
            selector.clearProduct();
        } else if (productHidden) {
            productHidden.value = '';
        }

        editBadge.classList.add('hidden');
        cancelEditButton.classList.add('hidden');
        addDirectButton.textContent = '+ Add Direct Item';
    }

    function buildHiddenInputs() {
        hiddenInputs.innerHTML = '';

        items.forEach(function (item, index) {
            const fields = {
                material_requirement_item_id: item.material_requirement_item_id || '',
                material_type_id: item.material_type_id,
                material_specification_id: '',
                material_grade_id: '',
                brand_master_id: item.brand_master_id || '',
                unit_master_id: item.unit_master_id,
                specification_text: item.specification_text || '',
                dispatched_quantity: item.dispatched_quantity,
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

            const sourceBadge = item.material_requirement_item_id
                ? `<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        ${escapeHtml(item.source_label || 'Material Requirement')}
                   </span>`
                : `<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        Direct HO Dispatch
                   </span>`;

            row.innerHTML = `
                <td class="px-3 py-2.5 text-center">
                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-50 px-2 text-xs font-bold text-blue-700">
                        ${index + 1}
                    </span>
                </td>
                <td class="px-3 py-2.5">
                    <div class="font-semibold text-gray-800">${escapeHtml(item.product_name)}</div>
                    ${item.product_code ? `<div class="mt-0.5 text-xs text-gray-400">${escapeHtml(item.product_code)}</div>` : ''}
                </td>
                <td class="px-3 py-2.5">
                    ${item.specification_text
                        ? `<span class="font-medium text-gray-800">${escapeHtml(item.specification_text)}</span>`
                        : '<span class="text-gray-400">-</span>'}
                </td>
                <td class="px-3 py-2.5">
                    ${item.brand_name ? escapeHtml(item.brand_name) : '<span class="text-gray-400">-</span>'}
                </td>
                <td class="px-3 py-2.5 text-right font-semibold text-blue-700">
                    ${formatQty(item.dispatched_quantity)}
                </td>
                <td class="px-3 py-2.5">${escapeHtml(item.unit_name || '')}</td>
                <td class="px-3 py-2.5">${sourceBadge}</td>
                <td class="px-3 py-2.5">
                    ${item.remarks ? escapeHtml(item.remarks) : '<span class="text-gray-400">-</span>'}
                </td>
                <td class="px-3 py-2.5">
                    <div class="flex justify-center gap-2">
                        ${!item.material_requirement_item_id
                            ? `<button type="button"
                                       class="edit-item rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                       data-index="${index}">
                                   Edit
                               </button>`
                            : ''}
                        <button type="button"
                                class="remove-item rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                data-index="${index}">
                            Remove
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

        itemCount.textContent = items.length;
        stickyItemCount.textContent = items.length;
        emptyState.classList.toggle('hidden', items.length > 0);
        buildHiddenInputs();
    }

    function normalizeInitialItems() {
        items = (initialItems || [])
            .filter(item => item.material_type_id)
            .map(function (item) {
                return {
                    id: item.id || '',
                    material_requirement_item_id: item.material_requirement_item_id || '',
                    material_type_id: Number(item.material_type_id),
                    product_name: item.product_name || `Product #${item.material_type_id}`,
                    product_code: item.product_code || '',
                    specification_text: item.specification_text || '',
                    brand_master_id: item.brand_master_id || '',
                    brand_name: item.brand_name || '',
                    unit_master_id: item.unit_master_id || '',
                    unit_name: item.unit_name || '',
                    dispatched_quantity: item.dispatched_quantity || '',
                    remarks: item.remarks || '',
                    source_label: item.source_label || (
                        item.material_requirement_item_id
                            ? 'Material Requirement'
                            : 'Direct HO Dispatch'
                    ),
                };
            });

        renderItems();
    }

    function isRequirementItemAlreadyAdded(requirementItemId) {
        return items.some(function (item) {
            return String(item.material_requirement_item_id) === String(requirementItemId);
        });
    }

    function addRequirementItem(item, requirement) {
        if (isRequirementItemAlreadyAdded(item.material_requirement_item_id)) {
            return false;
        }

        const quantity = Number(item.available_quantity || 0);

        if (!quantity || quantity <= 0) {
            return false;
        }

        items.push({
            material_requirement_item_id: item.material_requirement_item_id,
            material_type_id: Number(item.material_type_id),
            product_name: item.product_name || `Product #${item.material_type_id}`,
            product_code: item.product_code || '',
            specification_text: item.specification_text || '',
            brand_master_id: item.brand_master_id || '',
            brand_name: item.brand_name || '',
            unit_master_id: item.unit_master_id || '',
            unit_name: item.unit_name || '',
            dispatched_quantity: quantity,
            remarks: item.remarks || '',
            source_label: requirement.display_number || 'Material Requirement',
        });

        renderItems();
        return true;
    }

    function renderRequirements(requirements) {
        requirementsList.innerHTML = '';

        if (!requirements.length) {
            requirementsList.classList.add('hidden');
            requirementsMessage.classList.remove('hidden');
            requirementsMessage.textContent =
                'No approved Material Requirement items are currently available for HO Dispatch for this Project.';
            return;
        }

        requirementsMessage.classList.add('hidden');
        requirementsList.classList.remove('hidden');

        requirements.forEach(function (requirement) {
            const wrapper = document.createElement('div');
            wrapper.className = 'p-4';

            const details = document.createElement('details');
            details.className = 'group rounded-lg border border-gray-200 bg-white';
            details.open = requirements.length === 1;

            const summary = document.createElement('summary');
            summary.className =
                'flex cursor-pointer list-none flex-col gap-2 px-4 py-3 hover:bg-gray-50 md:flex-row md:items-center md:justify-between';

            summary.innerHTML = `
                <div>
                    <div class="font-bold text-gray-800">${escapeHtml(requirement.display_number)}</div>
                    <div class="mt-0.5 text-xs text-gray-500">
                        Required Date: ${escapeHtml(requirement.required_date || '-')}
                        &nbsp;•&nbsp;
                        Priority: ${escapeHtml(requirement.priority || 'Normal')}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        ${requirement.items.length} Available Item(s)
                    </span>
                    <button type="button"
                            class="add-all-requirement rounded-lg bg-[#10212F] px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                        Add All Pending
                    </button>
                </div>
            `;

            const body = document.createElement('div');
            body.className = 'border-t border-gray-200';

            const tableWrap = document.createElement('div');
            tableWrap.className = 'overflow-x-auto';

            const table = document.createElement('table');
            table.className = 'w-full min-w-[1100px] text-sm';

            table.innerHTML = `
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="min-w-[250px] px-3 py-2.5 text-left">Product</th>
                        <th class="min-w-[170px] px-3 py-2.5 text-left">Specification</th>
                        <th class="min-w-[130px] px-3 py-2.5 text-left">Brand</th>
                        <th class="w-24 px-3 py-2.5 text-right">Required</th>
                        <th class="w-24 px-3 py-2.5 text-right">Fulfilled</th>
                        <th class="w-24 px-3 py-2.5 text-right">PO Alloc.</th>
                        <th class="w-24 px-3 py-2.5 text-right">HO Alloc.</th>
                        <th class="w-24 px-3 py-2.5 text-right">Available</th>
                        <th class="w-24 px-3 py-2.5 text-left">Unit</th>
                        <th class="w-24 px-3 py-2.5 text-center">Action</th>
                    </tr>
                </thead>
            `;

            const tbody = document.createElement('tbody');
            tbody.className = 'divide-y divide-gray-200';

            requirement.items.forEach(function (item) {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                row.innerHTML = `
                    <td class="px-3 py-2.5 font-semibold text-gray-800">${escapeHtml(item.product_name)}</td>
                    <td class="px-3 py-2.5">
                        ${item.specification_text ? escapeHtml(item.specification_text) : '<span class="text-gray-400">-</span>'}
                    </td>
                    <td class="px-3 py-2.5">
                        ${item.brand_name ? escapeHtml(item.brand_name) : '<span class="text-gray-400">-</span>'}
                    </td>
                    <td class="px-3 py-2.5 text-right">${formatQty(item.required_quantity)}</td>
                    <td class="px-3 py-2.5 text-right">${formatQty(item.fulfilled_quantity)}</td>
                    <td class="px-3 py-2.5 text-right">${formatQty(item.po_allocated_quantity)}</td>
                    <td class="px-3 py-2.5 text-right">${formatQty(item.dispatch_allocated_quantity)}</td>
                    <td class="px-3 py-2.5 text-right font-bold text-emerald-700">${formatQty(item.available_quantity)}</td>
                    <td class="px-3 py-2.5">${escapeHtml(item.unit_name || '')}</td>
                    <td class="px-3 py-2.5 text-center">
                        <button type="button"
                                class="add-requirement-item rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                            Add
                        </button>
                    </td>
                `;

                const addButton = row.querySelector('.add-requirement-item');

                addButton.addEventListener('click', function () {
                    if (addRequirementItem(item, requirement)) {
                        this.disabled = true;
                        this.textContent = 'Added';
                        this.className =
                            'rounded-md border border-gray-200 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-400';
                    } else {
                        alert('This requirement item is already added, or no quantity is available.');
                    }
                });

                tbody.appendChild(row);
            });

            table.appendChild(tbody);
            tableWrap.appendChild(table);
            body.appendChild(tableWrap);

            summary.querySelector('.add-all-requirement')
                .addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    let added = 0;

                    requirement.items.forEach(function (item) {
                        if (
                            !isRequirementItemAlreadyAdded(item.material_requirement_item_id)
                            && addRequirementItem(item, requirement)
                        ) {
                            added++;
                        }
                    });

                    if (!added) {
                        alert('All available items from this requirement are already in the dispatch.');
                    }
                });

            details.appendChild(summary);
            details.appendChild(body);
            wrapper.appendChild(details);
            requirementsList.appendChild(wrapper);
        });
    }

    async function loadRequirements() {
        const projectId = projectSelect.value;

        if (!projectId) {
            requirementsList.classList.add('hidden');
            requirementsMessage.classList.remove('hidden');
            requirementsMessage.textContent =
                'Select a Project before loading Material Requirements.';
            projectSelect.focus();
            return;
        }

        requirementsButton.disabled = true;
        requirementsButton.textContent = 'Loading...';
        requirementsList.classList.add('hidden');
        requirementsMessage.classList.remove('hidden');
        requirementsMessage.textContent = 'Loading approved Material Requirements...';

        try {
            const url = new URL(requirementsUrl, window.location.origin);
            url.searchParams.set('project_id', projectId);

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) throw new Error('Unable to load Material Requirements.');

            const payload = await response.json();
            renderRequirements(payload.requirements || []);
        } catch (error) {
            console.error(error);
            requirementsList.classList.add('hidden');
            requirementsMessage.classList.remove('hidden');
            requirementsMessage.textContent =
                'Unable to load Material Requirements. Please try again.';
        } finally {
            requirementsButton.disabled = false;
            requirementsButton.textContent = 'Load Requirements';
        }
    }

    async function setDirectEntryForEdit(index) {
        const item = items[index];
        if (!item || item.material_requirement_item_id) return;

        editingIndex = index;
        specificationInput.value = item.specification_text || '';
        quantityInput.value = item.dispatched_quantity || '';
        remarksInput.value = item.remarks || '';
        unitSelect.value = item.unit_master_id || '';

        selectedProduct = {
            id: item.material_type_id,
            name: item.product_name,
            unit: item.unit_master_id ? {
                id: Number(item.unit_master_id),
                name: item.unit_name || '',
            } : null,
        };

        const selector = getProductSelectorData();

        if (selector) {
            selector.selectedProduct = selectedProduct;
            selector.selectedId = String(item.material_type_id);
            selector.query = item.product_name;
            selector.results = [];
            selector.open = false;
        }

        if (productHidden) productHidden.value = item.material_type_id;

        try {
            await loadProductDependencies(
                item.material_type_id,
                item.brand_master_id || '',
                item.brand_name || '',
                item.unit_master_id || ''
            );
        } catch (error) {
            console.error(error);
            rebuildBrands(item.brand_master_id || '', item.brand_name || '');
            unitSelect.value = item.unit_master_id || '';
        }

        editBadge.classList.remove('hidden');
        cancelEditButton.classList.remove('hidden');
        addDirectButton.textContent = 'Update Direct Item';
        specificationInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    form.addEventListener('ravion-product-selected', async function (event) {
        const product = event.detail?.product;
        if (!product) return;

        selectedProduct = product;

        try {
            const data = await loadProductDependencies(product.id);
            if (data?.unit) selectedProduct.unit = data.unit;
        } catch (error) {
            console.error(error);
            alert('Unable to load Product details. Please select the Product again.');
        }
    });

    form.addEventListener('ravion-product-cleared', function () {
        selectedProduct = null;
        availableBrands = [];
        rebuildBrands();
        unitSelect.value = '';
    });

    addDirectButton.addEventListener('click', function () {
        const productId = Number(productHidden?.value || 0);
        const quantity = Number(quantityInput.value || 0);
        const unitId = unitSelect.value;

        if (!productId) {
            alert('Select a Product first.');
            return;
        }

        if (!quantity || quantity <= 0) {
            alert('Enter a quantity greater than zero.');
            quantityInput.focus();
            return;
        }

        if (!unitId) {
            alert('Select a Unit.');
            unitSelect.focus();
            return;
        }

        const selector = getProductSelectorData();
        const productFromSelector = selector?.selectedProduct || selectedProduct;
        const selectedBrandOption = brandSelect.options[brandSelect.selectedIndex];

        const directItem = {
            id: editingIndex !== null ? (items[editingIndex]?.id || '') : '',
            material_requirement_item_id: '',
            material_type_id: productId,
            product_name:
                productFromSelector?.name
                || selectedProduct?.name
                || `Product #${productId}`,
            product_code:
                productFromSelector?.catalogue_code
                || productFromSelector?.code
                || '',
            specification_text: specificationInput.value.trim(),
            brand_master_id: brandSelect.value || '',
            brand_name: brandSelect.value
                ? (selectedBrandOption?.text || '')
                : '',
            unit_master_id: unitId,
            unit_name: getUnitName(unitId),
            dispatched_quantity: quantityInput.value,
            remarks: remarksInput.value.trim(),
            source_label: 'Direct HO Dispatch',
        };

        if (editingIndex !== null) {
            items[editingIndex] = directItem;
        } else {
            items.push(directItem);
        }

        renderItems();
        clearEntry();
    });

    cancelEditButton.addEventListener('click', clearEntry);

    tableBody.addEventListener('click', function (event) {
        const editButton = event.target.closest('.edit-item');
        const removeButton = event.target.closest('.remove-item');

        if (editButton) {
            setDirectEntryForEdit(Number(editButton.dataset.index));
            return;
        }

        if (removeButton) {
            const index = Number(removeButton.dataset.index);

            if (!confirm('Remove this Product from the dispatch?')) return;

            items.splice(index, 1);

            if (editingIndex === index) {
                clearEntry();
            } else if (editingIndex !== null && editingIndex > index) {
                editingIndex--;
            }

            renderItems();
        }
    });

    requirementsButton.addEventListener('click', loadRequirements);

    projectSelect.addEventListener('change', function () {
        requirementsList.classList.add('hidden');
        requirementsMessage.classList.remove('hidden');
        requirementsMessage.innerHTML =
            'Project changed. Click <strong>Load Requirements</strong> to view available approved items.';

        const selectedOption = projectSelect.options[projectSelect.selectedIndex];

        if (!deliveryAddress.value.trim() && selectedOption?.dataset?.location) {
            deliveryAddress.value = selectedOption.dataset.location;
        }
    });

    form.addEventListener('submit', function (event) {
        if (items.length === 0) {
            event.preventDefault();
            alert('Add at least one Product to the dispatch before saving.');
        }
    });

    normalizeInitialItems();

    if (projectSelect.value && !deliveryAddress.value.trim()) {
        const selectedOption = projectSelect.options[projectSelect.selectedIndex];
        if (selectedOption?.dataset?.location) {
            deliveryAddress.value = selectedOption.dataset.location;
        }
    }
});
</script>
