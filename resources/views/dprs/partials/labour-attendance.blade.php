@php
    $attendanceLookupUrl = route('ajax.dprs.labour-attendance');

    $linkedAttendanceIds = collect(
        old(
            'labour_attendance_ids',
            isset($dpr)
                ? $dpr->labourAttendances->pluck('id')->all()
                : []
        )
    )
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->values();
@endphp

<section
    id="dpr-labour-attendance-section"
    class="{{ $cardClass ?? 'bg-white rounded-lg shadow-sm p-4 mb-4' }}"
    data-lookup-url="{{ $attendanceLookupUrl }}"
    data-linked-attendance-ids='@json($linkedAttendanceIds)'
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                Labour Attendance
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Attendance is loaded automatically from the selected project and DPR date.
            </p>
        </div>

        <div
            id="dpr-attendance-status-badge"
            class="hidden rounded-full px-3 py-1 text-sm font-semibold"
        ></div>
    </div>

    {{-- Hidden attendance IDs submitted with the DPR form --}}
    <div id="dpr-attendance-hidden-inputs"></div>

    {{-- Initial instruction --}}
    <div
        id="dpr-attendance-initial-state"
        class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4"
    >
        <div class="flex items-start gap-3">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path d="M8 7V3m8 4V3M4 11h16"></path>
                    <rect x="4" y="5" width="16" height="16" rx="2"></rect>
                    <path d="m9 16 2 2 4-4"></path>
                </svg>
            </div>

            <div>
                <p class="font-semibold text-blue-900">
                    Select the DPR project and date
                </p>

                <p class="mt-1 text-sm text-blue-700">
                    Matching submitted or approved attendance sheets will appear here automatically.
                </p>
            </div>
        </div>
    </div>

    {{-- Loading state --}}
    <div
        id="dpr-attendance-loading-state"
        class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-4"
    >
        <div class="flex items-center gap-3">
            <svg
                class="h-5 w-5 animate-spin text-blue-600"
                viewBox="0 0 24 24"
                fill="none"
                aria-hidden="true"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                ></path>
            </svg>

            <div>
                <p class="font-semibold text-gray-800">
                    Loading Labour Attendance
                </p>

                <p class="text-sm text-gray-600">
                    Please wait while attendance records are retrieved.
                </p>
            </div>
        </div>
    </div>

    {{-- No attendance state --}}
    <div
        id="dpr-attendance-empty-state"
        class="mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 p-4"
    >
        <div class="flex items-start gap-3">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 8v4m0 4h.01"></path>
                </svg>
            </div>

            <div>
                <p class="font-semibold text-amber-900">
                    No Labour Attendance found
                </p>

                <p
                    id="dpr-attendance-empty-message"
                    class="mt-1 text-sm text-amber-800"
                >
                    No submitted or approved attendance sheet exists for the selected project and date.
                </p>
            </div>
        </div>
    </div>

    {{-- Error state --}}
    <div
        id="dpr-attendance-error-state"
        class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 p-4"
    >
        <div class="flex items-start gap-3">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 8v4m0 4h.01"></path>
                </svg>
            </div>

            <div class="flex-1">
                <p class="font-semibold text-red-900">
                    Unable to load attendance
                </p>

                <p
                    id="dpr-attendance-error-message"
                    class="mt-1 text-sm text-red-700"
                >
                    An unexpected error occurred while loading attendance.
                </p>

                <button
                    type="button"
                    id="dpr-attendance-retry-button"
                    class="mt-3 rounded bg-red-700 px-3 py-2 text-sm font-semibold text-white hover:bg-red-800"
                >
                    Retry
                </button>
            </div>
        </div>
    </div>

    {{-- Attendance summary --}}
    <div
        id="dpr-attendance-results"
        class="mt-4 hidden space-y-4"
    >
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Attendance Sheets
                </p>

                <p
                    id="dpr-attendance-sheet-count"
                    class="mt-1 text-2xl font-bold text-gray-900"
                >
                    0
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Total Labour
                </p>

                <p
                    id="dpr-attendance-labour-count"
                    class="mt-1 text-2xl font-bold text-gray-900"
                >
                    0
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Normal Hours
                </p>

                <p
                    id="dpr-attendance-normal-hours"
                    class="mt-1 text-2xl font-bold text-gray-900"
                >
                    0
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    OT Hours
                </p>

                <p
                    id="dpr-attendance-ot-hours"
                    class="mt-1 text-2xl font-bold text-gray-900"
                >
                    0
                </p>
            </div>
        </div>

        <div
            id="dpr-attendance-sheet-list"
            class="space-y-3"
        ></div>
    </div>
</section>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const attendanceSection = document.getElementById(
                'dpr-labour-attendance-section'
            );

            if (!attendanceSection) {
                return;
            }

            const projectSelect = document.querySelector(
                'select[name="project_id"]'
            );

            const dprDateInput = document.querySelector(
                'input[name="dpr_date"]'
            );

            if (!projectSelect || !dprDateInput) {
                return;
            }

            const lookupUrl = attendanceSection.dataset.lookupUrl;

            const initialState = document.getElementById(
                'dpr-attendance-initial-state'
            );

            const loadingState = document.getElementById(
                'dpr-attendance-loading-state'
            );

            const emptyState = document.getElementById(
                'dpr-attendance-empty-state'
            );

            const emptyMessage = document.getElementById(
                'dpr-attendance-empty-message'
            );

            const errorState = document.getElementById(
                'dpr-attendance-error-state'
            );

            const errorMessage = document.getElementById(
                'dpr-attendance-error-message'
            );

            const retryButton = document.getElementById(
                'dpr-attendance-retry-button'
            );

            const resultsContainer = document.getElementById(
                'dpr-attendance-results'
            );

            const statusBadge = document.getElementById(
                'dpr-attendance-status-badge'
            );

            const hiddenInputsContainer = document.getElementById(
                'dpr-attendance-hidden-inputs'
            );

            const sheetList = document.getElementById(
                'dpr-attendance-sheet-list'
            );

            const sheetCountElement = document.getElementById(
                'dpr-attendance-sheet-count'
            );

            const labourCountElement = document.getElementById(
                'dpr-attendance-labour-count'
            );

            const normalHoursElement = document.getElementById(
                'dpr-attendance-normal-hours'
            );

            const otHoursElement = document.getElementById(
                'dpr-attendance-ot-hours'
            );

            let activeRequestController = null;

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';

                return div.innerHTML;
            }

            function formatNumber(value) {
                const numericValue = Number(value ?? 0);

                if (!Number.isFinite(numericValue)) {
                    return '0';
                }

                return numericValue.toLocaleString(undefined, {
                    maximumFractionDigits: 2
                });
            }

            function hideAllStates() {
                initialState.classList.add('hidden');
                loadingState.classList.add('hidden');
                emptyState.classList.add('hidden');
                errorState.classList.add('hidden');
                resultsContainer.classList.add('hidden');
            }

            function clearAttendanceData() {
                hiddenInputsContainer.innerHTML = '';
                sheetList.innerHTML = '';

                sheetCountElement.textContent = '0';
                labourCountElement.textContent = '0';
                normalHoursElement.textContent = '0';
                otHoursElement.textContent = '0';

                statusBadge.className =
                    'hidden rounded-full px-3 py-1 text-sm font-semibold';

                statusBadge.textContent = '';
            }

            function showInitialState() {
                hideAllStates();
                clearAttendanceData();
                initialState.classList.remove('hidden');
            }

            function showLoadingState() {
                hideAllStates();
                clearAttendanceData();
                loadingState.classList.remove('hidden');

                statusBadge.className =
                    'rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700';

                statusBadge.textContent = 'Loading';
            }

            function showEmptyState(message) {
                hideAllStates();
                clearAttendanceData();

                emptyMessage.textContent =
                    message ||
                    'No submitted or approved attendance sheet exists for the selected project and date.';

                emptyState.classList.remove('hidden');

                statusBadge.className =
                    'rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700';

                statusBadge.textContent = 'Not Found';
            }

            function showErrorState(message) {
                hideAllStates();
                clearAttendanceData();

                errorMessage.textContent =
                    message ||
                    'An unexpected error occurred while loading attendance.';

                errorState.classList.remove('hidden');

                statusBadge.className =
                    'rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700';

                statusBadge.textContent = 'Error';
            }

            function createHiddenAttendanceInputs(attendanceIds) {
                hiddenInputsContainer.innerHTML = '';

                attendanceIds.forEach(function (attendanceId) {
                    const input = document.createElement('input');

                    input.type = 'hidden';
                    input.name = 'labour_attendance_ids[]';
                    input.value = attendanceId;

                    hiddenInputsContainer.appendChild(input);
                });
            }

            function createLabourDetailRows(details) {
                if (!Array.isArray(details) || details.length === 0) {
                    return `
                        <tr>
                            <td
                                colspan="7"
                                class="px-3 py-4 text-center text-sm text-gray-500"
                            >
                                No labour details are available for this attendance sheet.
                            </td>
                        </tr>
                    `;
                }

                return details.map(function (detail, index) {
                    return `
                        <tr class="border-t border-gray-200">
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-700">
                                ${index + 1}
                            </td>

                            <td class="px-3 py-2 text-sm">
                                <div class="font-semibold text-gray-900">
                                    ${escapeHtml(detail.labour_name || 'Unknown Labour')}
                                </div>

                                ${
                                    detail.labour_code
                                        ? `
                                            <div class="text-xs text-gray-500">
                                                ${escapeHtml(detail.labour_code)}
                                            </div>
                                        `
                                        : ''
                                }
                            </td>

                            <td class="px-3 py-2 text-sm text-gray-700">
                                ${escapeHtml(detail.designation || '—')}
                            </td>

                            <td class="px-3 py-2 text-sm text-gray-700">
                                ${escapeHtml(detail.contractor || 'Company Labour')}
                            </td>

                            <td class="px-3 py-2 text-sm text-gray-700">
                                ${escapeHtml(detail.attendance_status || 'Not Specified')}
                            </td>

                            <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-700">
                                ${formatNumber(detail.normal_hours)}
                            </td>

                            <td class="whitespace-nowrap px-3 py-2 text-right text-sm text-gray-700">
                                ${formatNumber(detail.ot_hours)}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            function createAttendanceCard(attendance, index) {
                const card = document.createElement('article');

                card.className =
                    'overflow-hidden rounded-lg border border-gray-200 bg-white';

                const statusClass = attendance.is_approved
                    ? 'bg-green-100 text-green-700'
                    : 'bg-blue-100 text-blue-700';

                const detailsId =
                    `dpr-attendance-details-${attendance.id}-${index}`;

                card.innerHTML = `
                    <div class="p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-gray-900">
                                        ${escapeHtml(attendance.attendance_number || 'Attendance')}
                                    </h3>

                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold ${statusClass}">
                                        ${escapeHtml(attendance.display_status || attendance.status || 'Unknown')}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-600">
                                    <span>
                                        <strong class="text-gray-800">Shift:</strong>
                                        ${escapeHtml(attendance.shift || 'General Shift')}
                                    </span>

                                    <span>
                                        <strong class="text-gray-800">Labour:</strong>
                                        ${formatNumber(attendance.total_labour)}
                                    </span>

                                    <span>
                                        <strong class="text-gray-800">Normal Hours:</strong>
                                        ${formatNumber(attendance.normal_hours)}
                                    </span>

                                    <span>
                                        <strong class="text-gray-800">OT Hours:</strong>
                                        ${formatNumber(attendance.ot_hours)}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="dpr-attendance-details-toggle rounded border border-blue-600 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50"
                                    data-target="${detailsId}"
                                >
                                    View Labour Details
                                </button>

                                ${
                                    attendance.view_url
                                        ? `
                                            <a
                                                href="${escapeHtml(attendance.view_url)}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                            >
                                                Open Attendance
                                            </a>
                                        `
                                        : ''
                                }
                            </div>
                        </div>
                    </div>

                    <div
                        id="${detailsId}"
                        class="hidden border-t border-gray-200 bg-gray-50 p-3"
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            #
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            Labour
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            Designation
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            Contractor
                                        </th>

                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            Status
                                        </th>

                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            Normal Hours
                                        </th>

                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            OT Hours
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    ${createLabourDetailRows(attendance.details)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;

                return card;
            }

            function bindAttendanceDetailToggles() {
                attendanceSection
                    .querySelectorAll('.dpr-attendance-details-toggle')
                    .forEach(function (button) {
                        button.addEventListener('click', function () {
                            const targetId = button.dataset.target;
                            const target = document.getElementById(targetId);

                            if (!target) {
                                return;
                            }

                            const isHidden = target.classList.contains('hidden');

                            target.classList.toggle('hidden');

                            button.textContent = isHidden
                                ? 'Hide Labour Details'
                                : 'View Labour Details';
                        });
                    });
            }

            function renderAttendanceResults(payload) {
                hideAllStates();
                clearAttendanceData();

                const attendances = Array.isArray(payload.attendances)
                    ? payload.attendances
                    : [];

                if (attendances.length === 0) {
                    showEmptyState(payload.message);
                    return;
                }

                const attendanceIds = Array.isArray(payload.attendance_ids)
                    ? payload.attendance_ids
                    : attendances
                        .map(function (attendance) {
                            return attendance.id;
                        })
                        .filter(Boolean);

                createHiddenAttendanceInputs(attendanceIds);

                let totalLabour = 0;
                let totalNormalHours = 0;
                let totalOtHours = 0;

                attendances.forEach(function (attendance, index) {
                    totalLabour += Number(attendance.total_labour ?? 0);
                    totalNormalHours += Number(attendance.normal_hours ?? 0);
                    totalOtHours += Number(attendance.ot_hours ?? 0);

                    sheetList.appendChild(
                        createAttendanceCard(attendance, index)
                    );
                });

                sheetCountElement.textContent =
                    formatNumber(attendances.length);

                labourCountElement.textContent =
                    formatNumber(totalLabour);

                normalHoursElement.textContent =
                    formatNumber(totalNormalHours);

                otHoursElement.textContent =
                    formatNumber(totalOtHours);

                resultsContainer.classList.remove('hidden');

                statusBadge.className =
                    'rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700';

                statusBadge.textContent =
                    attendances.length === 1
                        ? '1 Sheet Linked'
                        : `${attendances.length} Sheets Linked`;

                bindAttendanceDetailToggles();
            }

            async function loadLabourAttendance() {
                const projectId = projectSelect.value;
                const attendanceDate = dprDateInput.value;

                if (!projectId || !attendanceDate) {
                    if (activeRequestController) {
                        activeRequestController.abort();
                    }

                    showInitialState();
                    return;
                }

                if (activeRequestController) {
                    activeRequestController.abort();
                }

                activeRequestController = new AbortController();

                showLoadingState();

                const parameters = new URLSearchParams({
                    project_id: projectId,
                    attendance_date: attendanceDate
                });

                try {
                    const response = await fetch(
                        `${lookupUrl}?${parameters.toString()}`,
                        {
                            method: 'GET',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            signal: activeRequestController.signal
                        }
                    );

                    const payload = await response.json().catch(function () {
                        return {};
                    });

                    if (!response.ok) {
                        const message =
                            payload.message ||
                            'The attendance request could not be completed.';

                        throw new Error(message);
                    }

                    if (!payload.success) {
                        throw new Error(
                            payload.message ||
                            'The attendance request was unsuccessful.'
                        );
                    }

                    if (!payload.attendance_found) {
                        showEmptyState(payload.message);
                        return;
                    }

                    renderAttendanceResults(payload);
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    showErrorState(error.message);
                }
            }

            projectSelect.addEventListener(
                'change',
                loadLabourAttendance
            );

            dprDateInput.addEventListener(
                'change',
                loadLabourAttendance
            );

            retryButton.addEventListener(
                'click',
                loadLabourAttendance
            );

            if (projectSelect.value && dprDateInput.value) {
                loadLabourAttendance();
            } else {
                showInitialState();
            }
        });
    </script>
@endonce