@php
    $oldRows = collect(old('manual_labours', []))
        ->filter(fn ($row) => is_array($row) && !empty($row['labour_id']))
        ->keyBy(fn ($row) => (string) $row['labour_id']);

    $reasonOptions = [
        'missed_attendance' => 'Labour missed in attendance',
        'late_joining' => 'Labour joined after attendance',
        'replacement_labour' => 'Replacement labour',
        'emergency_labour' => 'Emergency labour engagement',
        'attendance_not_created' => 'Attendance sheet was not created',
        'attendance_incomplete' => 'Attendance sheet was incomplete',
        'other' => 'Other justified reason',
    ];

    $categories = collect($labourCategories ?? [])->sortBy(
        fn ($item) => strtolower($item->category_name ?? $item->name ?? '')
    );

    $types = collect($labourTypes ?? [])->sortBy(
        fn ($item) => strtolower($item->labour_type_name ?? $item->name ?? '')
    );

    $activeLabours = collect($labours ?? [])->sortBy(
        fn ($item) => strtolower(($item->labour_code ?? '') . ' ' . ($item->full_name ?? ''))
    );

    $statusItems = collect($attendanceStatuses ?? [])->map(function ($status) {
        $name = $status->name ?? $status->status_name ?? $status->code ?? 'Status';
        $code = strtoupper((string) ($status->code ?? $name));

        $kind = match (true) {
            in_array($code, ['P', 'PRESENT'], true) => 'present',
            in_array($code, ['A', 'ABSENT'], true) => 'absent',
            in_array($code, ['HD', 'HALF DAY', 'HALF_DAY', 'HALFDAY'], true) => 'half_day',
            default => 'other',
        };

        return compact('status', 'name', 'code', 'kind');
    });
@endphp

<section
    id="dpr-manual-labour-section"
    class="{{ $cardClass ?? 'mb-4 overflow-hidden rounded-xl border border-amber-200 bg-white shadow-sm' }}"
>
    <div class="border-b border-amber-200 bg-amber-50 px-4 py-4 sm:px-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-900">Attendance Exceptions</h2>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                        Manual DPR Addition
                    </span>
                </div>

                <p class="mt-1 text-sm text-gray-600">
                    Select only labourers who are missing from the linked Labour Attendance.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span id="manual-visible-count" class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-700 ring-1 ring-amber-200">
                    0 Available
                </span>
                <span id="manual-selected-count" class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">
                    0 Selected
                </span>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-amber-200 bg-white/80 p-3 text-sm text-amber-900">
            <span class="font-semibold">Reason is mandatory.</span>
            Remarks are optional. Included rows remain fully editable before submission.
        </div>
    </div>

    @if($errors->has('manual_labours') || $errors->has('manual_labours.*'))
        <div class="border-b border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:px-6">
            Please correct the highlighted Attendance Exception fields.
        </div>
    @endif

    <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="manual-category-filter" class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                <select id="manual-category-filter" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->category_name ?? $category->name ?? 'Category' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="manual-type-filter" class="mb-1 block text-sm font-medium text-gray-700">Trade / Labour Type</label>
                <select id="manual-type-filter" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Trades</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" data-category-id="{{ $type->labour_category_id }}">
                            {{ $type->labour_type_name ?? $type->type_name ?? $type->name ?? 'Labour Type' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="manual-search" class="mb-1 block text-sm font-medium text-gray-700">Search Labour</label>
                <div class="flex gap-2">
                    <input
                        id="manual-search"
                        type="search"
                        class="min-w-0 flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Name, code or mobile"
                        autocomplete="off"
                    >
                    <button id="manual-clear-filters" type="button" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="manual-attendance-exclusion-note" class="hidden border-b border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 sm:px-6">
        Labour already present in linked attendance has been hidden from this list.
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-[1320px] w-full divide-y divide-gray-200">
            <thead class="sticky top-0 z-10 bg-gray-100">
                <tr>
                    <th class="w-16 px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">Include</th>
                    <th class="min-w-48 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Labour</th>
                    <th class="min-w-32 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Category</th>
                    <th class="min-w-32 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Trade</th>
                    <th class="min-w-56 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                    <th class="min-w-36 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Shift</th>
                    <th class="w-28 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Hours</th>
                    <th class="w-24 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">OT</th>
                    <th class="min-w-48 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Reason</th>
                    <th class="min-w-56 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Remarks</th>
                </tr>
            </thead>

            <tbody id="manual-grid-body" class="divide-y divide-gray-200 bg-white">
                @foreach($activeLabours as $labour)
                    @php
                        $rowKey = (string) $labour->id;
                        $oldRow = $oldRows->get($rowKey, []);
                        $included = !empty($oldRow);
                        $name = $labour->full_name ?? 'Labour';
                        $code = $labour->labour_code ?? '';
                        $mobile = $labour->mobile ?? '';
                        $categoryName = $labour->labourCategory?->category_name ?? $labour->labourCategory?->name ?? '-';
                        $typeName = $labour->labourType?->labour_type_name ?? $labour->labourType?->name ?? '-';
                        $designation = $labour->designationRole?->name ?? $labour->designationRole?->designation_name ?? null;
                        $selectedStatus = (string) ($oldRow['attendance_status_id'] ?? '');
                        $searchText = strtolower(trim($code . ' ' . $name . ' ' . $mobile . ' ' . $categoryName . ' ' . $typeName . ' ' . ($designation ?? '')));
                    @endphp

                    <tr
                        class="manual-grid-row transition {{ $included ? 'bg-amber-50' : 'opacity-60' }}"
                        data-labour-id="{{ $labour->id }}"
                        data-category-id="{{ $labour->labour_category_id }}"
                        data-type-id="{{ $labour->labour_type_id }}"
                        data-default-shift-id="{{ $labour->default_shift_id }}"
                        data-search="{{ $searchText }}"
                    >
                        <td class="px-3 py-3 text-center align-top">
                            <input
                                type="checkbox"
                                class="manual-include h-5 w-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                @checked($included)
                            >

                            <input
                                type="hidden"
                                class="manual-submitted-field"
                                name="manual_labours[{{ $labour->id }}][labour_id]"
                                value="{{ $labour->id }}"
                                @disabled(!$included)
                            >
                        </td>

                        <td class="px-3 py-3 align-top">
                            <p class="font-semibold text-gray-900">{{ $name }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $code ?: 'No Code' }}
                                @if($designation) · {{ $designation }} @endif
                            </p>
                            @if($mobile)
                                <p class="mt-1 text-xs text-gray-500">{{ $mobile }}</p>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-sm text-gray-700 align-top">{{ $categoryName }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700 align-top">{{ $typeName }}</td>

                        <td class="px-3 py-3 align-top">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($statusItems as $statusItem)
                                    @php
                                        $statusId = (string) $statusItem['status']->id;
                                        $baseClass = match($statusItem['kind']) {
                                            'present' => 'border-green-200 text-green-700 hover:bg-green-50',
                                            'half_day' => 'border-yellow-200 text-yellow-700 hover:bg-yellow-50',
                                            'absent' => 'border-red-200 text-red-700 hover:bg-red-50',
                                            default => 'border-gray-200 text-gray-700 hover:bg-gray-50',
                                        };

                                        $activeClass = match($statusItem['kind']) {
                                            'present' => 'bg-green-100 ring-1 ring-green-400',
                                            'half_day' => 'bg-yellow-100 ring-1 ring-yellow-400',
                                            'absent' => 'bg-red-100 ring-1 ring-red-400',
                                            default => 'bg-gray-100 ring-1 ring-gray-400',
                                        };
                                    @endphp

                                    <button
                                        type="button"
                                        class="manual-status-button rounded-md border px-2.5 py-1.5 text-xs font-semibold transition {{ $baseClass }} {{ $selectedStatus === $statusId ? $activeClass : 'bg-white' }}"
                                        data-status-id="{{ $statusId }}"
                                        data-status-kind="{{ $statusItem['kind'] }}"
                                        @disabled(!$included)
                                    >
                                        {{ $statusItem['name'] }}
                                    </button>
                                @endforeach
                            </div>

                            <input
                                type="hidden"
                                class="manual-submitted-field manual-status-input"
                                name="manual_labours[{{ $labour->id }}][attendance_status_id]"
                                value="{{ $selectedStatus }}"
                                @disabled(!$included)
                            >

                            @error("manual_labours.{$labour->id}.attendance_status_id")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        <td class="px-3 py-3 align-top">
                            <select
                                name="manual_labours[{{ $labour->id }}][shift_id]"
                                class="manual-submitted-field manual-shift w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @disabled(!$included)
                            >
                                <option value="">Select Shift</option>
                                @foreach($shifts as $shift)
                                    <option
                                        value="{{ $shift->id }}"
                                        @selected((string) ($oldRow['shift_id'] ?? $labour->default_shift_id ?? '') === (string) $shift->id)
                                    >
                                        {{ $shift->shift_name ?? $shift->name ?? $shift->code ?? 'Shift' }}
                                    </option>
                                @endforeach
                            </select>

                            @error("manual_labours.{$labour->id}.shift_id")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        <td class="px-3 py-3 align-top">
                            <input
                                type="number"
                                name="manual_labours[{{ $labour->id }}][normal_hours]"
                                value="{{ $oldRow['normal_hours'] ?? '' }}"
                                min="0"
                                max="24"
                                step="0.5"
                                placeholder="8"
                                class="manual-submitted-field manual-hours w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @disabled(!$included)
                            >

                            @error("manual_labours.{$labour->id}.normal_hours")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        <td class="px-3 py-3 align-top">
                            <input
                                type="number"
                                name="manual_labours[{{ $labour->id }}][ot_hours]"
                                value="{{ $oldRow['ot_hours'] ?? '0' }}"
                                min="0"
                                max="24"
                                step="0.5"
                                placeholder="0"
                                class="manual-submitted-field manual-ot w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @disabled(!$included)
                            >

                            @error("manual_labours.{$labour->id}.ot_hours")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        <td class="px-3 py-3 align-top">
                            <select
                                name="manual_labours[{{ $labour->id }}][reason]"
                                class="manual-submitted-field w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @disabled(!$included)
                            >
                                <option value="">Select Reason</option>
                                @foreach($reasonOptions as $reasonValue => $reasonLabel)
                                    <option value="{{ $reasonValue }}" @selected(($oldRow['reason'] ?? '') === $reasonValue)>
                                        {{ $reasonLabel }}
                                    </option>
                                @endforeach
                            </select>

                            @error("manual_labours.{$labour->id}.reason")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>

                        <td class="px-3 py-3 align-top">
                            <textarea
                                name="manual_labours[{{ $labour->id }}][remarks]"
                                rows="2"
                                maxlength="1000"
                                placeholder="Optional remarks"
                                class="manual-submitted-field w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @disabled(!$included)
                            >{{ $oldRow['remarks'] ?? '' }}</textarea>

                            @error("manual_labours.{$labour->id}.remarks")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="manual-empty-state" class="hidden border-t border-gray-200 px-6 py-12 text-center">
        <p class="font-semibold text-gray-800">No eligible labourers found</p>
        <p class="mt-1 text-sm text-gray-500">Change the filters or confirm that the labourer is not already in linked attendance.</p>
    </div>
</section>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    const section = document.getElementById('dpr-manual-labour-section');

    if (!section) {
        return;
    }

    const rows = Array.from(section.querySelectorAll('.manual-grid-row'));
    const categoryFilter = document.getElementById('manual-category-filter');
    const typeFilter = document.getElementById('manual-type-filter');
    const searchInput = document.getElementById('manual-search');
    const clearButton = document.getElementById('manual-clear-filters');
    const visibleCount = document.getElementById('manual-visible-count');
    const selectedCount = document.getElementById('manual-selected-count');
    const emptyState = document.getElementById('manual-empty-state');
    const gridBody = document.getElementById('manual-grid-body');
    const exclusionNote = document.getElementById('manual-attendance-exclusion-note');
    const attendanceLabourIds = new Set();

    function activeClasses(kind) {
        if (kind === 'present') return ['bg-green-100', 'ring-1', 'ring-green-400'];
        if (kind === 'half_day') return ['bg-yellow-100', 'ring-1', 'ring-yellow-400'];
        if (kind === 'absent') return ['bg-red-100', 'ring-1', 'ring-red-400'];
        return ['bg-gray-100', 'ring-1', 'ring-gray-400'];
    }

    function clearStatus(row) {
        row.querySelectorAll('.manual-status-button').forEach(function (button) {
            button.classList.remove(
                'bg-green-100', 'ring-green-400',
                'bg-yellow-100', 'ring-yellow-400',
                'bg-red-100', 'ring-red-400',
                'bg-gray-100', 'ring-gray-400',
                'ring-1'
            );
            button.classList.add('bg-white');
        });
    }

    function selectStatus(row, button, setDefaults) {
        const statusInput = row.querySelector('.manual-status-input');
        const hoursInput = row.querySelector('.manual-hours');
        const otInput = row.querySelector('.manual-ot');

        clearStatus(row);
        button.classList.remove('bg-white');
        button.classList.add(...activeClasses(button.dataset.statusKind));
        statusInput.value = button.dataset.statusId;

        if (!setDefaults) return;

        if (button.dataset.statusKind === 'present') {
            hoursInput.value = '8';
            otInput.value = '0';
        } else if (button.dataset.statusKind === 'half_day') {
            hoursInput.value = '4';
            otInput.value = '0';
        } else if (button.dataset.statusKind === 'absent') {
            hoursInput.value = '0';
            otInput.value = '0';
        }
    }

    function setEnabled(row, enabled) {
        row.classList.toggle('bg-amber-50', enabled);
        row.classList.toggle('opacity-60', !enabled);

        row.querySelectorAll('.manual-submitted-field, .manual-status-button').forEach(function (field) {
            field.disabled = !enabled;
        });

        if (enabled) {
            const shift = row.querySelector('.manual-shift');
            const ot = row.querySelector('.manual-ot');

            if (!shift.value && row.dataset.defaultShiftId) {
                shift.value = row.dataset.defaultShiftId;
            }

            if (ot.value === '') {
                ot.value = '0';
            }
        }
    }

    function updateTradeOptions() {
        const categoryId = categoryFilter.value;
        let selectedStillAvailable = false;

        Array.from(typeFilter.options).forEach(function (option) {
            if (!option.value) return;

            const available = !categoryId || option.dataset.categoryId === categoryId;
            option.hidden = !available;
            option.disabled = !available;

            if (available && option.value === typeFilter.value) {
                selectedStillAvailable = true;
            }
        });

        if (typeFilter.value && !selectedStillAvailable) {
            typeFilter.value = '';
        }
    }

    function rowMatches(row) {
        if (attendanceLabourIds.has(row.dataset.labourId)) return false;

        const categoryId = categoryFilter.value;
        const typeId = typeFilter.value;
        const search = searchInput.value.trim().toLowerCase();

        return (!categoryId || row.dataset.categoryId === categoryId)
            && (!typeId || row.dataset.typeId === typeId)
            && (!search || (row.dataset.search || '').includes(search));
    }

    function applyFilters() {
        let shown = 0;

        rows.forEach(function (row) {
            const visible = rowMatches(row);
            row.classList.toggle('hidden', !visible);
            if (visible) shown++;
        });

        visibleCount.textContent = `${shown} Available`;
        emptyState.classList.toggle('hidden', shown > 0);
        gridBody.classList.toggle('hidden', shown === 0);
        exclusionNote.classList.toggle('hidden', attendanceLabourIds.size === 0);
    }

    function updateSelectedCount() {
        const count = rows.filter(function (row) {
            const checkbox = row.querySelector('.manual-include');
            return checkbox.checked && !attendanceLabourIds.has(row.dataset.labourId);
        }).length;

        selectedCount.textContent = `${count} Selected`;
    }

    function excludeAttendanceLabours(ids) {
        attendanceLabourIds.clear();

        (ids || []).forEach(function (id) {
            if (id !== null && id !== undefined) {
                attendanceLabourIds.add(String(id));
            }
        });

        rows.forEach(function (row) {
            const checkbox = row.querySelector('.manual-include');
            const excluded = attendanceLabourIds.has(row.dataset.labourId);

            checkbox.disabled = excluded;

            if (excluded) {
                checkbox.checked = false;
                setEnabled(row, false);
            }
        });

        applyFilters();
        updateSelectedCount();
    }

    function discoverAttendanceLabours() {
        const ids = new Set();

        document.querySelectorAll('[data-attendance-labour-id]').forEach(function (element) {
            if (element.dataset.attendanceLabourId) {
                ids.add(String(element.dataset.attendanceLabourId));
            }
        });

        if (ids.size) {
            excludeAttendanceLabours(Array.from(ids));
        }
    }

    rows.forEach(function (row) {
        const checkbox = row.querySelector('.manual-include');
        setEnabled(row, checkbox.checked);

        checkbox.addEventListener('change', function () {
            setEnabled(row, checkbox.checked);

            if (checkbox.checked && !row.querySelector('.manual-status-input').value) {
                const presentButton = row.querySelector('.manual-status-button[data-status-kind="present"]');
                if (presentButton) selectStatus(row, presentButton, true);
            }

            updateSelectedCount();
        });

        row.querySelectorAll('.manual-status-button').forEach(function (button) {
            button.addEventListener('click', function () {
                selectStatus(row, button, true);
            });
        });
    });

    categoryFilter.addEventListener('change', function () {
        updateTradeOptions();
        applyFilters();
    });

    typeFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);

    clearButton.addEventListener('click', function () {
        categoryFilter.value = '';
        typeFilter.value = '';
        searchInput.value = '';
        updateTradeOptions();
        applyFilters();
    });

    window.updateManualLabourAttendanceExclusions = excludeAttendanceLabours;

    document.addEventListener('dpr:labour-attendance-loaded', function (event) {
        excludeAttendanceLabours(event.detail?.labourIds ?? event.detail?.labour_ids ?? []);
    });

    document.addEventListener('dpr:attendance-labours-updated', function (event) {
        excludeAttendanceLabours(event.detail?.labourIds ?? event.detail?.labour_ids ?? []);
    });

    new MutationObserver(discoverAttendanceLabours).observe(document.body, {
        childList: true,
        subtree: true
    });

    updateTradeOptions();
    discoverAttendanceLabours();
    applyFilters();
    updateSelectedCount();
});
</script>
@endonce