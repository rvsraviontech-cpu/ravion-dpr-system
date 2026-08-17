<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('material-items-body');
    const addRowButton = document.getElementById('add-item-row');

    const photoRows = document.getElementById('photo-rows');
    const addPhotoRowButton = document.getElementById('add-photo-row');

    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');

    const contractorCheckbox =
        document.getElementById('supplied_by_contractor');

    const contractorWrapper =
        document.getElementById('contractor-wrapper');

    let rowIndex = body.querySelectorAll('.material-item-row').length;
    let photoIndex = photoRows.querySelectorAll('.photo-row').length;

    const activityOptions = @json($activityOptionsForJs);
    const materialTypeOptions = @json($materialTypeOptionsForJs);
    const brandOptions = @json($brandOptionsForJs);
    const specificationOptions = @json($specificationOptionsForJs);
    const gradeOptions = @json($gradeOptionsForJs);
    const materialGroups = @json($materialGroupsForJs);
    const photoTypes = @json($photoTypes);

    function option(value, label, selected = false) {
        return new Option(label, value, selected, selected);
    }

    function rebuildSelect(
        select,
        placeholder,
        values,
        selectedValue = ''
    ) {
        select.innerHTML = '';
        select.add(option('', placeholder));

        values.forEach(function (item) {
            select.add(
                option(
                    String(item.id),
                    item.name,
                    String(item.id) === String(selectedValue)
                )
            );
        });
    }

    function initializeRow(row) {
        const divisionSelect =
            row.querySelector('.activity-division-select');

        const activitySelect =
            row.querySelector('.activity-select');

        const groupSelect =
            row.querySelector('.material-group-select');

        const typeSelect =
            row.querySelector('.material-type-select');

        const brandSelect =
            row.querySelector('.brand-select');

        const specificationSelect =
            row.querySelector('.specification-select');

        const gradeSelect =
            row.querySelector('.grade-select');

        const unitIdInput =
            row.querySelector('.unit-id-input');

        const unitNameInput =
            row.querySelector('.unit-name-input');

        const currentActivityId = activitySelect.value;
        const currentTypeId = typeSelect.value;
        const currentBrandId = brandSelect.value;
        const currentSpecificationId = specificationSelect.value;
        const currentGradeId = gradeSelect.value;

        function filterActivities(preserve = false) {
            const divisionId = divisionSelect.value;

            const filtered = activityOptions.filter(function (activity) {
                return divisionId === ''
                    || String(activity.division_id) === String(divisionId);
            });

            rebuildSelect(
                activitySelect,
                'Select Activity',
                filtered,
                preserve ? currentActivityId : ''
            );
        }

        function filterMaterialTypes(preserve = false) {
            const group = groupSelect.value;

            const filtered = materialTypeOptions.filter(function (type) {
                return group === ''
                    || type.group === group;
            });

            rebuildSelect(
                typeSelect,
                'Select Material Type',
                filtered,
                preserve ? currentTypeId : ''
            );

            updateDependentMaterialFields(preserve);
        }

        function updateDependentMaterialFields(preserve = false) {
            const materialTypeId = typeSelect.value;

            const selectedType = materialTypeOptions.find(function (type) {
                return String(type.id) === String(materialTypeId);
            });

            unitIdInput.value = selectedType?.unit_id || '';
            unitNameInput.value = selectedType?.unit_name || '';

            const filteredBrands = brandOptions.filter(function (brand) {
                return String(brand.material_type_id)
                    === String(materialTypeId);
            });

            const filteredSpecifications =
                specificationOptions.filter(function (specification) {
                    return String(specification.material_type_id)
                        === String(materialTypeId);
                });

            const filteredGrades = gradeOptions.filter(function (grade) {
                return String(grade.material_type_id)
                    === String(materialTypeId);
            });

            rebuildSelect(
                brandSelect,
                'Select Brand',
                filteredBrands,
                preserve ? currentBrandId : ''
            );

            rebuildSelect(
                specificationSelect,
                'Select Specification',
                filteredSpecifications,
                preserve ? currentSpecificationId : ''
            );

            rebuildSelect(
                gradeSelect,
                'Select Grade / Rating',
                filteredGrades,
                preserve ? currentGradeId : ''
            );

            refreshPhotoItemOptions();
        }

        divisionSelect.addEventListener('change', function () {
            filterActivities(false);
        });

        groupSelect.addEventListener('change', function () {
            filterMaterialTypes(false);
            refreshPhotoItemOptions();
        });

        typeSelect.addEventListener('change', function () {
            updateDependentMaterialFields(false);
            refreshPhotoItemOptions();
        });

        row.querySelector('.remove-item-row')
            .addEventListener('click', function () {
                const rows = body.querySelectorAll('.material-item-row');

                if (rows.length <= 1) {
                    alert('At least one material row is required.');
                    return;
                }

                row.remove();

                /*
                 * Keep item array indices aligned with the controller's
                 * photos[*][item_index] mapping.
                 */
                renumberMaterialRows();
                refreshPhotoItemOptions();
            });

        const selectedType = materialTypeOptions.find(function (type) {
            return String(type.id) === String(currentTypeId);
        });

        if (selectedType && !groupSelect.value) {
            groupSelect.value = selectedType.group || '';
        }

        filterActivities(true);
        filterMaterialTypes(true);
    }

    function buildNewRow(index) {
        const row = document.createElement('tr');

        row.className = 'material-item-row block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:table-row lg:rounded-none lg:border-0 lg:shadow-none';
        row.dataset.rowIndex = index;

        row.innerHTML = `
            <td class="block bg-slate-50 px-3 py-3 lg:table-cell lg:bg-transparent lg:text-center">
                <div class="flex items-center justify-between lg:block">
                    <span class="text-xs font-bold uppercase tracking-wide text-gray-500 lg:hidden">Material Item</span>
                    <span class="row-number inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 lg:bg-transparent lg:text-sm lg:text-inherit"></span>
                </div>
            </td>

            <td data-mobile-label="Activity Division" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][activity_division_id]"
                        class="{{ $inputClass }} activity-division-select">
                    <option value="">Select Division</option>

                    @foreach($activityDivisions as $division)
                        <option value="{{ $division->id }}">
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td data-mobile-label="Activity / Material" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][activity_id]"
                        class="{{ $inputClass }} activity-select">
                    <option value="">Select Activity</option>
                </select>
            </td>

            <td data-mobile-label="Material Group" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select class="{{ $inputClass }} material-group-select">
                    <option value="">Select Group</option>

                    ${materialGroups.map(function (group) {
                        return `<option value="${escapeHtml(group)}">${escapeHtml(group)}</option>`;
                    }).join('')}
                </select>
            </td>

            <td data-mobile-label="Material Type" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][material_type_id]"
                        class="{{ $inputClass }} material-type-select"
                        required>
                    <option value="">Select Material Type</option>
                </select>
            </td>

            <td data-mobile-label="Brand" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][brand_master_id]"
                        class="{{ $inputClass }} brand-select">
                    <option value="">Select Brand</option>
                </select>
            </td>

            <td data-mobile-label="Specification" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][material_specification_id]"
                        class="{{ $inputClass }} specification-select">
                    <option value="">Select Specification</option>
                </select>
            </td>

            <td data-mobile-label="Grade / Rating" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <select name="items[${index}][material_grade_id]"
                        class="{{ $inputClass }} grade-select">
                    <option value="">Select Grade / Rating</option>
                </select>
            </td>

            <td data-mobile-label="Quantity" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="number"
                       step="0.001"
                       min="0.001"
                       name="items[${index}][quantity_received]"
                       class="{{ $inputClass }}"
                       required>
            </td>

            <td data-mobile-label="Unit" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="hidden"
                       name="items[${index}][unit_master_id]"
                       class="unit-id-input">

                <input type="text"
                       class="{{ $inputClass }} unit-name-input bg-gray-100"
                       readonly
                       placeholder="Auto">
            </td>

            <td data-mobile-label="Remarks" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                <input type="text"
                       name="items[${index}][remarks]"
                       class="{{ $inputClass }}"
                       placeholder="Optional">
            </td>

            <td data-mobile-label="Action" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:text-center lg:before:hidden">
                <button type="button"
                        class="remove-item-row w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 lg:w-auto lg:border-0 lg:bg-red-600 lg:text-white lg:hover:bg-red-700">
                    Remove
                </button>
            </td>
        `;

        return row;
    }

    function renumberMaterialRows() {
        body.querySelectorAll('.material-item-row')
            .forEach(function (row, index) {
                row.dataset.rowIndex = index;

                row.querySelector('.row-number').textContent = index + 1;

                row.querySelectorAll('[name^="items["]')
                    .forEach(function (field) {
                        field.name = field.name.replace(
                            /^items\[\d+\]/,
                            `items[${index}]`
                        );
                    });
            });

        rowIndex = body.querySelectorAll('.material-item-row').length;
    }

    function refreshRowNumbers() {
        body.querySelectorAll('.material-item-row')
            .forEach(function (row, index) {
                row.querySelector('.row-number').textContent = index + 1;
            });
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function materialItemOptions() {
        return Array.from(
            body.querySelectorAll('.material-item-row')
        ).map(function (row, index) {
            const typeSelect =
                row.querySelector('.material-type-select');

            const selectedOption =
                typeSelect.options[typeSelect.selectedIndex];

            const label = typeSelect.value
                ? `Item ${index + 1} — ${selectedOption?.text || 'Material'}`
                : `Item ${index + 1} — Material not selected`;

            return {
                value: String(index),
                label: label,
            };
        });
    }

    function refreshPhotoItemOptions() {
        const options = materialItemOptions();

        photoRows.querySelectorAll('.photo-item-select')
            .forEach(function (select) {
                const selected =
                    select.value !== ''
                        ? select.value
                        : (select.dataset.selected || '');

                select.innerHTML = '';
                select.add(
                    new Option(
                        'General / Whole Receipt',
                        ''
                    )
                );

                options.forEach(function (item) {
                    select.add(
                        new Option(
                            item.label,
                            item.value,
                            false,
                            String(item.value) === String(selected)
                        )
                    );
                });

                select.dataset.selected = select.value;
            });
    }

    function initializePhotoRow(row) {
        const fileInput =
            row.querySelector('.photo-file-input');

        const previewWrapper =
            row.querySelector('.photo-preview-wrapper');

        const previewImage =
            row.querySelector('.photo-preview');

        const fileName =
            row.querySelector('.photo-file-name');

        const fileSize =
            row.querySelector('.photo-file-size');

        const itemSelect =
            row.querySelector('.photo-item-select');

        itemSelect.addEventListener('change', function () {
            itemSelect.dataset.selected = itemSelect.value;
        });

        fileInput.addEventListener('change', function () {
            const file = fileInput.files?.[0];

            if (! file) {
                previewWrapper.classList.add('hidden');
                previewImage.removeAttribute('src');
                fileName.textContent = '';
                fileSize.textContent = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('Each photo must be 10 MB or smaller.');
                fileInput.value = '';
                previewWrapper.classList.add('hidden');
                return;
            }

            const objectUrl = URL.createObjectURL(file);

            previewImage.src = objectUrl;
            fileName.textContent = file.name;
            fileSize.textContent =
                `${(file.size / 1024 / 1024).toFixed(2)} MB`;

            previewWrapper.classList.remove('hidden');

            previewImage.onload = function () {
                URL.revokeObjectURL(objectUrl);
            };
        });

        row.querySelector('.remove-photo-row')
            .addEventListener('click', function () {
                const rows =
                    photoRows.querySelectorAll('.photo-row');

                if (rows.length <= 1) {
                    /*
                     * Keep one empty upload row for convenience.
                     */
                    fileInput.value = '';
                    row.querySelector('.photo-type-select').value =
                        'Material Photo';

                    row.querySelector('input[type="text"]').value = '';
                    itemSelect.value = '';
                    itemSelect.dataset.selected = '';

                    previewWrapper.classList.add('hidden');
                    return;
                }

                row.remove();
                renumberPhotoRows();
            });
    }

    function buildPhotoRow(index) {
        const row = document.createElement('div');

        row.className = 'photo-row p-3 sm:p-5';
        row.dataset.photoIndex = index;

        row.innerHTML = `
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                <div class="lg:col-span-2">
                    <label class="{{ $labelClass }}">Photo Type</label>

                    <select name="photos[${index}][photo_type]"
                            class="{{ $inputClass }} photo-type-select">
                        ${photoTypes.map(function (type) {
                            return `<option value="${escapeHtml(type)}">${escapeHtml(type)}</option>`;
                        }).join('')}
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelClass }}">Material Item</label>

                    <select name="photos[${index}][item_index]"
                            class="{{ $inputClass }} photo-item-select"
                            data-selected="">
                        <option value="">General / Whole Receipt</option>
                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        Choose a material only when the photo relates specifically to that item.
                    </p>
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelClass }}">Caption</label>

                    <input type="text"
                           name="photos[${index}][caption]"
                           class="{{ $inputClass }}"
                           maxlength="500"
                           placeholder="Optional photo description">
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelClass }}">Image</label>

                    <input type="file"
                           name="photos[${index}][file]"
                           class="{{ $inputClass }} photo-file-input"
                           accept="image/jpeg,image/png,image/webp,image/*">

                    <p class="mt-1 text-xs text-gray-500">
                        JPG, PNG or WEBP. Maximum 10 MB.
                    </p>
                </div>

                <div class="lg:col-span-1 lg:pt-6">
                    <button type="button"
                            class="remove-photo-row w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Remove
                    </button>
                </div>

            </div>

            <div class="photo-preview-wrapper mt-4 hidden">
                <div class="flex flex-wrap items-start gap-4 rounded-lg border border-gray-200 bg-gray-50 p-3">

                    <img src=""
                         alt="Photo preview"
                         class="photo-preview h-28 w-36 rounded-lg border border-gray-200 bg-white object-cover">

                    <div class="text-sm text-gray-600">
                        <p class="font-semibold text-gray-800">Preview</p>
                        <p class="photo-file-name mt-1"></p>
                        <p class="photo-file-size mt-1 text-xs text-gray-500"></p>
                    </div>

                </div>
            </div>
        `;

        return row;
    }

    function renumberPhotoRows() {
        photoRows.querySelectorAll('.photo-row')
            .forEach(function (row, index) {
                row.dataset.photoIndex = index;

                row.querySelectorAll('[name^="photos["]')
                    .forEach(function (field) {
                        field.name = field.name.replace(
                            /^photos\[\d+\]/,
                            `photos[${index}]`
                        );
                    });
            });

        photoIndex = photoRows.querySelectorAll('.photo-row').length;
        refreshPhotoItemOptions();
    }

    function cloneSelectOptions(select) {
        return Array.from(select.options)
            .map(function (item) {
                return item.cloneNode(true);
            });
    }

    const originalBlockOptions = cloneSelectOptions(blockSelect);
    const originalFloorOptions = cloneSelectOptions(floorSelect);
    const originalUnitOptions = cloneSelectOptions(unitSelect);

    function filterLocationSelect(
        select,
        predicate,
        placeholder
    ) {
        const currentValue = select.value;

        const source =
            select === blockSelect
                ? originalBlockOptions
                : select === floorSelect
                    ? originalFloorOptions
                    : originalUnitOptions;

        select.innerHTML = '';
        select.add(new Option(placeholder, ''));

        source.forEach(function (item) {
            if (item.value !== '' && predicate(item)) {
                const cloned = item.cloneNode(true);

                if (cloned.value === currentValue) {
                    cloned.selected = true;
                }

                select.add(cloned);
            }
        });
    }

    function filterProjectLocations() {
        const projectId = projectSelect.value;

        filterLocationSelect(
            blockSelect,
            function (option) {
                return projectId === ''
                    || option.dataset.project === projectId;
            },
            'Select Block'
        );

        filterFloors();
    }

    function filterFloors() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;

        filterLocationSelect(
            floorSelect,
            function (option) {
                const projectMatch =
                    projectId === ''
                    || option.dataset.project === projectId;

                const blockMatch =
                    blockId === ''
                    || option.dataset.block === blockId;

                return projectMatch && blockMatch;
            },
            'Select Floor'
        );

        filterUnits();
    }

    function filterUnits() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;
        const floorId = floorSelect.value;

        filterLocationSelect(
            unitSelect,
            function (option) {
                const projectMatch =
                    projectId === ''
                    || option.dataset.project === projectId;

                const blockMatch =
                    blockId === ''
                    || option.dataset.block === blockId;

                const floorMatch =
                    floorId === ''
                    || option.dataset.floor === floorId;

                return projectMatch && blockMatch && floorMatch;
            },
            'Select Unit'
        );
    }

    function toggleContractor() {
        contractorWrapper.classList.toggle(
            'hidden',
            ! contractorCheckbox.checked
        );

        if (! contractorCheckbox.checked) {
            document.getElementById('contractor_id').value = '';
        }
    }

    addRowButton.addEventListener('click', function () {
        const newRow = buildNewRow(rowIndex++);
        body.appendChild(newRow);
        initializeRow(newRow);
        renumberMaterialRows();
        refreshPhotoItemOptions();
    });

    addPhotoRowButton.addEventListener('click', function () {
        const newPhotoRow = buildPhotoRow(photoIndex++);
        photoRows.appendChild(newPhotoRow);
        initializePhotoRow(newPhotoRow);
        renumberPhotoRows();
    });

    projectSelect.addEventListener('change', function () {
        blockSelect.value = '';
        floorSelect.value = '';
        unitSelect.value = '';
        filterProjectLocations();
    });

    blockSelect.addEventListener('change', function () {
        floorSelect.value = '';
        unitSelect.value = '';
        filterFloors();
    });

    floorSelect.addEventListener('change', function () {
        unitSelect.value = '';
        filterUnits();
    });

    contractorCheckbox.addEventListener(
        'change',
        toggleContractor
    );

    body.querySelectorAll('.material-item-row')
        .forEach(initializeRow);

    photoRows.querySelectorAll('.photo-row')
        .forEach(initializePhotoRow);

    renumberMaterialRows();
    renumberPhotoRows();

    filterProjectLocations();
    toggleContractor();
    refreshPhotoItemOptions();
});
</script>
