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

    const materialTypeOptions = @json($materialTypeOptionsForJs);
    const brandOptions = @json($brandOptionsForJs);
    const specificationOptions = @json($specificationOptionsForJs);
    const gradeOptions = @json($gradeOptionsForJs);
    const photoTypes = @json($photoTypes);

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

        tempName.required = mode === 'temporary';
        tempUnit.required = mode === 'temporary';
        typeSelect.required = mode === 'existing';

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
            unitId.value = selected?.unit_id || '';
            unitName.value = selected?.unit_name || '';

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

        temporaryUnit.addEventListener('change', () => unitId.value = temporaryUnit.value);

        row.querySelector('.temporary-material-name').addEventListener('input', refreshPhotoItemOptions);

        row.querySelector('.remove-item-row').addEventListener('click', function () {
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
        const row = body.querySelector('.material-item-row').cloneNode(true);
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

    addRowButton.addEventListener('click', function () {
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

    refreshLocations();
    refreshPhotoItemOptions();
});
</script>
