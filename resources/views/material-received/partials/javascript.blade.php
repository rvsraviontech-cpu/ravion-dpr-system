<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('material-items-body');
    const addRowButton = document.getElementById('add-item-row');
    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');
    const photoRows = document.getElementById('photo-rows');
    const addPhotoRowButton = document.getElementById('add-photo-row');
    const receiptSource = document.getElementById('receipt_source');
    const purchaseOrderSelect = document.getElementById('purchase_order_id');
    const purchaseOrderWrap = document.getElementById('purchase-order-source-wrap');
    const loadPurchaseOrderButton = document.getElementById('load-purchase-order');

    const materialTypeOptions = @json($materialTypeOptionsForJs);
    const brandOptions = @json($brandOptionsForJs);
    const specificationOptions = @json($specificationOptionsForJs);
    const gradeOptions = @json($gradeOptionsForJs);
    const photoTypes = @json($photoTypes);

    const directMode = document.getElementById('direct-entry-host') !== null;
    const entryHost = document.getElementById('direct-entry-host');
    const summaryBody = document.getElementById('direct-summary-body');
    let editingDirectRow = null;
    let rowIndex = body.querySelectorAll('.material-item-row').length;
    let photoIndex = photoRows ? photoRows.querySelectorAll('.photo-row').length : 0;

    function option(value, label, selected = false) {
        return new Option(label, value, selected, selected);
    }

    function rebuildSelect(select, placeholder, values, selectedValue = '') {
        select.innerHTML = '';
        select.add(option('', placeholder));
        values.forEach(item => select.add(option(String(item.id), item.name, String(item.id) === String(selectedValue))));
    }

    function materialById(id) {
        return materialTypeOptions.find(item => String(item.id) === String(id));
    }

    function setMode(row, mode, clearOther = false) {
        const entry = row.querySelector('.entry-mode-input');
        const existingPanel = row.querySelector('.existing-panel');
        const temporaryPanel = row.querySelector('.temporary-panel');
        const existingFields = row.querySelectorAll('.existing-field');
        const temporaryFields = row.querySelectorAll('.temporary-field');
        const tempName = row.querySelector('.temporary-material-name');
        const tempUnit = row.querySelector('.temporary-unit-select');
        const typeSelect = row.querySelector('.material-type-select');

        entry.value = mode;
        existingPanel.classList.toggle('hidden', mode !== 'existing');
        temporaryPanel.classList.toggle('hidden', mode !== 'temporary');
        existingFields.forEach(el => el.classList.toggle('hidden', mode !== 'existing'));
        temporaryFields.forEach(el => el.classList.toggle('hidden', mode !== 'temporary'));

        // Direct Receipt uses explicit Add/Save validation. Hidden controls must not
        // block the browser's submit event before our handler can run.
        tempName.required = !directMode && mode === 'temporary';
        tempUnit.required = !directMode && mode === 'temporary';
        typeSelect.required = !directMode && mode === 'existing';

        if (clearOther && mode === 'temporary') {
            typeSelect.value = '';
            row.querySelector('.material-search-input').value = '';
            row.querySelector('.category-display').value = 'Pending Classification';
            row.querySelector('.brand-select').innerHTML = '<option value="">Brand</option>';
            row.querySelector('.specification-select').innerHTML = '<option value="">Specification</option>';
            row.querySelector('.grade-select').innerHTML = '<option value="">Grade / Rating</option>';
            row.querySelector('.unit-id-input').value = '';
            row.querySelector('.unit-name-input').value = '';
        }

        if (clearOther && mode === 'existing') {
            tempName.value = '';
            row.querySelector('[name$="[temporary_brand]"]').value = '';
            row.querySelector('[name$="[temporary_specification]"]').value = '';
            row.querySelector('[name$="[temporary_grade]"]').value = '';
            row.querySelector('[name$="[temporary_classification_notes]"]').value = '';
            tempUnit.value = '';
            row.querySelector('.unit-id-input').value = '';
            row.querySelector('.category-display').value = '';
        }

        refreshPhotoItemOptions();
    }

    function initializeRow(row) {
        const searchInput = row.querySelector('.material-search-input');
        const searchResults = row.querySelector('.material-search-results');
        const typeSelect = row.querySelector('.material-type-select');
        const category = row.querySelector('.category-display');
        const brand = row.querySelector('.brand-select');
        const specification = row.querySelector('.specification-select');
        const grade = row.querySelector('.grade-select');
        const unitId = row.querySelector('.unit-id-input');
        const unitName = row.querySelector('.unit-name-input');
        const temporaryUnit = row.querySelector('.temporary-unit-select');
        const quantity = row.querySelector('.quantity-input');
        const transactionUnit = row.querySelector('.transaction-unit-select');
        const accepted = row.querySelector('.accepted-input');
        const shortQty = row.querySelector('.short-input');
        const damaged = row.querySelector('.damaged-input');
        const rejected = row.querySelector('.rejected-input');

        const initialMode = row.querySelector('.entry-mode-input').value || 'existing';
        const initialTypeId = typeSelect.value;
        const initialBrandId = @json(null);
        const initialSpecId = @json(null);
        const initialGradeId = @json(null);

        // Preserve server-rendered selected values before rebuilding filtered selects.
        const serverBrand = row.querySelector('[name$="[brand_master_id]"] option:checked')?.value || '';
        const serverSpec = row.querySelector('[name$="[material_specification_id]"] option:checked')?.value || '';
        const serverGrade = row.querySelector('[name$="[material_grade_id]"] option:checked')?.value || '';

        function updateDependencies(preserve = {}) {
            const selected = materialById(typeSelect.value);
            category.value = selected?.group || '';
            // Product Master unit is only the default. Never overwrite a saved/PO transaction unit.
            unitName.value = selected?.unit_name || '';
            if (transactionUnit) {
                if (!transactionUnit.value) {
                    transactionUnit.value = selected?.unit_id || '';
                }
                unitId.value = transactionUnit.value || '';
            } else {
                unitId.value = selected?.unit_id || '';
            }

            rebuildSelect(brand, 'Brand', brandOptions.filter(x => String(x.material_type_id) === String(typeSelect.value)), preserve.brandId || '');
            rebuildSelect(specification, 'Specification', specificationOptions.filter(x => String(x.material_type_id) === String(typeSelect.value)), preserve.specificationId || '');
            rebuildSelect(grade, 'Grade / Rating', gradeOptions.filter(x => String(x.material_type_id) === String(typeSelect.value)), preserve.gradeId || '');

            if (selected) searchInput.value = selected.name;
            refreshPhotoItemOptions();
        }

        function chooseMaterial(type) {
            typeSelect.value = String(type.id);
            updateDependencies();
            searchInput.value = type.name;
            searchResults.classList.add('hidden');
            quantity.focus();
        }

        function showSearchResults() {
            const term = searchInput.value.trim().toLowerCase();
            if (term.length < 1) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }

            const matches = materialTypeOptions
                .filter(type => (type.search || type.name.toLowerCase()).includes(term))
                .slice(0, 20);

            searchResults.innerHTML = '';
            if (!matches.length) {
                const empty = document.createElement('div');
                empty.className = 'px-4 py-3 text-sm text-gray-500';
                empty.textContent = 'No material found. Use “Material Not Found”.';
                searchResults.appendChild(empty);
            } else {
                matches.forEach(type => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'block w-full border-b border-gray-100 px-4 py-3 text-left hover:bg-blue-50';
                    button.innerHTML = `<div class="font-semibold text-gray-800">${type.name}</div><div class="text-xs text-gray-500">${type.group || 'Uncategorised'}${type.unit_name ? ' · ' + type.unit_name : ''}</div>`;
                    button.addEventListener('click', () => chooseMaterial(type));
                    searchResults.appendChild(button);
                });
            }
            searchResults.classList.remove('hidden');
        }

        searchInput.addEventListener('input', showSearchResults);
        searchInput.addEventListener('focus', showSearchResults);
        typeSelect.addEventListener('change', () => updateDependencies());

        row.querySelector('.enable-temporary-material').addEventListener('click', () => {
            setMode(row, 'temporary', true);
            row.querySelector('.temporary-material-name').focus();
        });

        row.querySelector('.use-existing-material').addEventListener('click', () => {
            setMode(row, 'existing', true);
            searchInput.focus();
        });

        temporaryUnit.addEventListener('change', () => { unitId.value = temporaryUnit.value; if (transactionUnit) transactionUnit.value = temporaryUnit.value; });
        transactionUnit?.addEventListener('change', () => unitId.value = transactionUnit.value);

        let acceptedAuto = !accepted?.value;
        accepted?.addEventListener('input', () => acceptedAuto = false);
        quantity?.addEventListener('input', () => {
            if (accepted && acceptedAuto) accepted.value = quantity.value;
        });

        row.querySelector('.temporary-material-name').addEventListener('input', refreshPhotoItemOptions);

        row.querySelector('.remove-item-row').addEventListener('click', function () {
            if (directMode) { removeDirectRow(row); return; }
            if (body.querySelectorAll('.material-item-row').length <= 1) {
                alert('At least one material row is required.');
                return;
            }
            row.remove();
            renumberMaterialRows();
            refreshPhotoItemOptions();
        });

        document.addEventListener('click', event => {
            if (!row.contains(event.target)) searchResults.classList.add('hidden');
        });

        setMode(row, initialMode, false);
        if (transactionUnit?.value) unitId.value = transactionUnit.value;

        if (initialMode === 'temporary') {
            temporaryUnit.value = unitId.value || '';
            category.value = 'Pending Classification';
        } else if (initialTypeId) {
            updateDependencies({
                brandId: serverBrand,
                specificationId: serverSpec,
                gradeId: serverGrade,
            });
        }
    }

    function buildNewRow(index) {
        const row = (body.querySelector('.material-item-row') || entryHost?.querySelector('.material-item-row')).cloneNode(true);
        row.dataset.rowIndex = index;

        row.querySelectorAll('input, select').forEach(field => {
            if (field.classList.contains('entry-mode-input')) {
                field.value = 'existing';
            } else if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else if (field.type !== 'button') {
                field.value = '';
            }
            field.required = false;
            field.disabled = false;
        });

        row.querySelector('.material-search-results').innerHTML = '';
        row.querySelector('.material-search-results').classList.add('hidden');
        return row;
    }

    function renumberMaterialRows() {
        body.querySelectorAll('.material-item-row').forEach((row, index) => {
            row.dataset.rowIndex = index;
            row.querySelector('.row-number').textContent = index + 1;
            row.querySelectorAll('[name^="items["]').forEach(field => {
                field.name = field.name.replace(/^items\[\d+\]/, `items[${index}]`);
            });
        });
        rowIndex = body.querySelectorAll('.material-item-row').length;
    }

    function materialItemOptions() {
        return Array.from(body.querySelectorAll('.material-item-row')).map((row, index) => {
            const mode = row.querySelector('.entry-mode-input').value;
            let label = 'Material not selected';
            if (mode === 'temporary') {
                label = row.querySelector('.temporary-material-name').value.trim() || 'Temporary material';
                label += ' — Pending Classification';
            } else {
                const type = materialById(row.querySelector('.material-type-select').value);
                if (type) label = type.name;
            }
            return {value: String(index), label: `Item ${index + 1} — ${label}`};
        });
    }

    function refreshPhotoItemOptions() {
        if (!photoRows) return;
        const options = materialItemOptions();
        photoRows.querySelectorAll('.photo-item-select').forEach(select => {
            const selected = select.value !== '' ? select.value : (select.dataset.selected || '');
            select.innerHTML = '';
            select.add(new Option('General / Whole Receipt', ''));
            options.forEach(item => select.add(new Option(item.label, item.value, false, String(item.value) === String(selected))));
            select.dataset.selected = select.value;
        });
    }

    function filterLocationSelect(select, predicates) {
        if (!select) return;
        Array.from(select.options).forEach((opt, i) => {
            if (i === 0) return;
            opt.hidden = !predicates.every(([key, value]) => !value || String(opt.dataset[key] || '') === String(value));
        });
        if (select.selectedOptions[0]?.hidden) select.value = '';
    }

    function refreshLocations() {
        const project = projectSelect?.value || '';
        const block = blockSelect?.value || '';
        const floor = floorSelect?.value || '';
        filterLocationSelect(blockSelect, [['project', project]]);
        filterLocationSelect(floorSelect, [['project', project], ['block', block]]);
        filterLocationSelect(unitSelect, [['project', project], ['block', block], ['floor', floor]]);
    }

    [projectSelect, blockSelect, floorSelect].forEach(select => select?.addEventListener('change', refreshLocations));

    body.querySelectorAll('.material-item-row').forEach(initializeRow);
    if (directMode) setupDirectReceipt();

    addRowButton.addEventListener('click', function () {
        if (directMode) return;
        const row = buildNewRow(rowIndex++);
        body.appendChild(row);
        renumberMaterialRows();
        initializeRow(row);
        row.querySelector('.material-search-input').focus();
        refreshPhotoItemOptions();
    });

    if (photoRows && addPhotoRowButton) {
        addPhotoRowButton.addEventListener('click', function () {
            const first = photoRows.querySelector('.photo-row');
            if (!first) return;
            const row = first.cloneNode(true);
            row.querySelectorAll('input, select').forEach(field => {
                if (field.type === 'file' || field.type === 'text') field.value = '';
                if (field.tagName === 'SELECT') field.selectedIndex = 0;
                if (field.name) field.name = field.name.replace(/^photos\[\d+\]/, `photos[${photoIndex}]`);
            });
            row.querySelector('.photo-row-number')?.replaceChildren(document.createTextNode(photoIndex + 1));
            photoRows.appendChild(row);
            photoIndex++;
            refreshPhotoItemOptions();
        });
    }

    function setupDirectReceipt() {
        const initialRows = Array.from(body.querySelectorAll('.material-item-row'));
        // The default blank row becomes the entry form; old() rows stay committed.
        const initialIsBlank = initialRows.length === 1 && !initialRows[0].querySelector('.material-type-select').value && !initialRows[0].querySelector('.temporary-material-name').value.trim();
        if (initialIsBlank) {
            entryHost.appendChild(initialRows[0]);
            clearDirectDraftRequired();
        } else createDirectDraft();
        refreshDirectSummary();
        document.getElementById('commit-direct-item').addEventListener('click', commitDirectItem);
        document.getElementById('cancel-direct-edit').addEventListener('click', () => {
            if (!editingDirectRow) return;
            // Editing is performed in-place; cancel returns the row to the list.
            body.appendChild(editingDirectRow);
            editingDirectRow = null;
            renumberMaterialRows();
            createDirectDraft();
            refreshDirectSummary();
        });
    }

    function clearDirectDraftRequired() {
        // The entry row is a draft, not a receipt item. Browser validation
        // happens before the submit event, so a hidden required draft input
        // otherwise blocks Save without letting our submit handler run.
        entryHost?.querySelectorAll('.material-item-row [required]').forEach(field => {
            field.required = false;
        });
    }

    function createDirectDraft() {
        const template = body.querySelector('.material-item-row') || entryHost.querySelector('.material-item-row');
        if (!template) return;
        const draft = buildNewRow(rowIndex++);
        draft.querySelector('.short-input').value = '0';
        draft.querySelector('.damaged-input').value = '0';
        draft.querySelector('.rejected-input').value = '0';
        entryHost.replaceChildren(draft);
        initializeRow(draft);
        clearDirectDraftRequired();
        draft.querySelector('.material-search-input').focus();
        document.getElementById('commit-direct-item').textContent = '+ Add to Receipt';
        document.getElementById('cancel-direct-edit').classList.add('hidden');
        document.getElementById('direct-entry-status').textContent = 'Choose a product and enter its receipt details';
    }

    function validateDirectEntry(row) {
        const mode = row.querySelector('.entry-mode-input').value;
        if (mode === 'temporary' ? !row.querySelector('.temporary-material-name').value.trim() : !row.querySelector('.material-type-select').value) return 'Select a product or enter the Material Not Found name.';
        const received = Number(row.querySelector('.quantity-input').value);
        const unit = row.querySelector('.unit-id-input').value;
        if (!(received > 0)) return 'Enter a Receive Now quantity greater than zero.';
        if (!unit) return 'Select a unit.';
        const accepted = Number(row.querySelector('.accepted-input').value || 0);
        const damaged = Number(row.querySelector('.damaged-input').value || 0);
        const rejected = Number(row.querySelector('.rejected-input').value || 0);
        if ([accepted, damaged, rejected, Number(row.querySelector('.short-input').value || 0)].some(x => x < 0)) return 'Quantities cannot be negative.';
        if (Math.abs(received - accepted - damaged - rejected) > 0.0005) return 'Receive Now must equal Accepted + Damaged + Rejected.';
        return '';
    }

    function commitDirectItem() {
        const row = entryHost.querySelector('.material-item-row');
        if (!row) return;
        const error = validateDirectEntry(row);
        if (error) { alert(error); return; }
        body.appendChild(row);
        editingDirectRow = null;
        renumberMaterialRows();
        createDirectDraft();
        refreshDirectSummary();
        refreshPhotoItemOptions();
    }

    function editDirectRow(row) {
        if (editingDirectRow) { alert('Update the material currently being edited first.'); return; }
        const draft = entryHost.querySelector('.material-item-row');
        if (draft) draft.remove();
        editingDirectRow = row;
        entryHost.appendChild(row);
        document.getElementById('commit-direct-item').textContent = 'Update Material';
        document.getElementById('cancel-direct-edit').classList.remove('hidden');
        document.getElementById('direct-entry-status').textContent = 'Editing an item already in this receipt';
        document.getElementById('direct-entry-card').scrollIntoView({behavior:'smooth',block:'start'});
    }

    function removeDirectRow(row) {
        if (editingDirectRow === row) { alert('Finish editing this material first.'); return; }
        if (body.querySelectorAll('.material-item-row').length <= 1 && !confirm('Remove the last material? You must add another before saving.')) return;
        const removedIndex = Array.from(body.querySelectorAll('.material-item-row')).indexOf(row);
        row.remove();
        // Photos previously associated with a removed item become general photos;
        // subsequent item indices shift down to follow their original material.
        photoRows?.querySelectorAll('.photo-item-select').forEach(select => {
            if (select.value === '') return;
            const value = Number(select.value);
            select.value = value === removedIndex ? '' : String(value > removedIndex ? value - 1 : value);
            select.dataset.selected = select.value;
        });
        renumberMaterialRows();
        refreshDirectSummary();
        refreshPhotoItemOptions();
    }

    function refreshDirectSummary() {
        if (!directMode) return;
        summaryBody.replaceChildren();
        const rows = Array.from(body.querySelectorAll('.material-item-row'));
        document.getElementById('direct-item-count').textContent = `(${rows.length})`;
        document.getElementById('direct-empty-message').classList.toggle('hidden', rows.length > 0);
        rows.forEach((row, index) => {
            const mode = row.querySelector('.entry-mode-input').value;
            const product = mode === 'temporary' ? row.querySelector('.temporary-material-name').value + ' — Pending Classification' : row.querySelector('.material-search-input').value;
            const spec = mode === 'temporary' ? row.querySelector('[name$="[temporary_specification]"]').value : row.querySelector('.specification-select').selectedOptions[0]?.textContent;
            const grade = mode === 'temporary' ? row.querySelector('[name$="[temporary_grade]"]').value : row.querySelector('.grade-select').selectedOptions[0]?.textContent;
            const brand = mode === 'temporary' ? row.querySelector('[name$="[temporary_brand]"]').value : row.querySelector('.brand-select').selectedOptions[0]?.textContent;
            const unit = row.querySelector('.transaction-unit-select').selectedOptions[0]?.textContent || '';
            const tr = document.createElement('tr');
            const cell = (value, cls='') => { const td = document.createElement('td'); td.className = 'px-3 py-3 ' + cls; td.textContent = value || '—'; tr.appendChild(td); return td; };
            cell(String(index + 1));
            const detail = [spec,grade,brand].filter(v => v && !['Specification','Grade / Rating','Brand'].includes(v)).join(' · ');
            const productCell = cell(product); productCell.classList.add('font-semibold','text-slate-800');
            if (detail) { const small = document.createElement('div'); small.className = 'mt-1 text-xs font-normal text-slate-500'; small.textContent = detail; productCell.appendChild(small); }
            cell(`${row.querySelector('.quantity-input').value} ${unit}`, 'text-right');
            cell(row.querySelector('.accepted-input').value, 'text-right');
            cell(row.querySelector('[name$="[remarks]"]').value);
            const actions = document.createElement('td'); actions.className = 'whitespace-nowrap px-3 py-3';
            const edit = document.createElement('button'); edit.type='button'; edit.className='mr-3 font-semibold text-blue-700 hover:underline'; edit.textContent='Edit'; edit.addEventListener('click',()=>editDirectRow(row));
            const remove = document.createElement('button'); remove.type='button'; remove.className='font-semibold text-red-700 hover:underline'; remove.textContent='Remove'; remove.addEventListener('click',()=>removeDirectRow(row));
            actions.append(edit,remove); tr.appendChild(actions); summaryBody.appendChild(tr);
        });
    }

    function refreshReceiptSource() {
        const isPo = receiptSource?.value === 'PO';
        purchaseOrderWrap?.classList.toggle('hidden', !isPo);
        if (!isPo && purchaseOrderSelect) purchaseOrderSelect.value = '';
    }

    receiptSource?.addEventListener('change', refreshReceiptSource);
    loadPurchaseOrderButton?.addEventListener('click', function () {
        if (!purchaseOrderSelect?.value) { alert('Select a Purchase Order first.'); return; }
        const url = new URL(window.location.href);
        url.searchParams.set('purchase_order_id', purchaseOrderSelect.value);
        window.location.href = url.toString();
    });

    document.getElementById('material-receipt-form')?.addEventListener('submit', function (event) {
        let valid = true;
        body.querySelectorAll('.material-item-row').forEach((row, index) => {
            if (directMode) {
                const entryError = validateDirectEntry(row);
                if (entryError) {
                    alert(`Row ${index + 1}: ${entryError}`);
                    valid = false;
                    return;
                }
            }
            const received = Number(row.querySelector('.quantity-input')?.value || 0);
            const acceptedQty = Number(row.querySelector('.accepted-input')?.value || 0);
            const damagedQty = Number(row.querySelector('.damaged-input')?.value || 0);
            const rejectedQty = Number(row.querySelector('.rejected-input')?.value || 0);
            const shortValue = Number(row.querySelector('.short-input')?.value || 0);
            if (Math.abs(received - (acceptedQty + damagedQty + rejectedQty)) > 0.0005) {
                alert(`Row ${index + 1}: Receive Now must equal Accepted + Damaged + Rejected.`);
                valid = false;
                return;
            }
            const pendingEl = row.querySelector('.po-pending-quantity');
            if (pendingEl) {
                const pending = Number((pendingEl.textContent || '0').replace(/,/g, ''));
                if ((received + shortValue) - pending > 0.0005) {
                    alert(`Row ${index + 1}: Receive Now + Short exceeds Pending quantity.`);
                    valid = false;
                }
            }
        });
        if (directMode) {
            if (editingDirectRow) { event.preventDefault(); alert('Click Update Material before saving the receipt.'); return; }
            if (!body.querySelector('.material-item-row')) { event.preventDefault(); alert('Add at least one material to the receipt.'); return; }
            // Uncommitted entry inputs are outside the submitted item list.
            entryHost.querySelectorAll('[name^="items["]').forEach(field => field.disabled = true);
        }
        if (!valid) { event.preventDefault(); if (directMode) entryHost.querySelectorAll('[name^="items["]').forEach(field => field.disabled = false); }
    });

    refreshReceiptSource();
    refreshLocations();
    refreshPhotoItemOptions();
});
</script>
