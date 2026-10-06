@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                New Equipment Movement
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Allocate, transfer or return machinery and equipment.
            </p>
        </div>

        <a href="{{ route('machinery-equipment-allocations.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Back to Movements
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <div class="font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('machinery-equipment-allocations.store') }}"
          id="movementForm">

        @csrf

        <div class="space-y-6">

            {{-- Equipment --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    1. Select Equipment
                </h2>

                <div class="mt-4">
                    <label for="machinery_equipment_id"
                           class="mb-1 block text-sm font-medium text-gray-700">
                        Equipment <span class="text-red-600">*</span>
                    </label>

                    <select name="machinery_equipment_id"
                            id="machinery_equipment_id"
                            required
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">

                        <option value="">Select Equipment</option>

                        @foreach ($equipment as $item)
                            <option value="{{ $item->id }}"
                                @selected(
                                    (string) old(
                                        'machinery_equipment_id',
                                        $selectedEquipment?->id
                                    ) === (string) $item->id
                                )>
                                {{ $item->equipment_code }}
                                — {{ $item->display_name }}
                                ({{ ucfirst($item->tracking_mode) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Equipment Summary --}}
                <div id="equipmentSummary"
                     class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-4">

                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Equipment
                            </div>
                            <div id="summaryEquipment"
                                 class="mt-1 text-sm font-semibold text-gray-900">
                                —
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Tracking
                            </div>
                            <div id="summaryTracking"
                                 class="mt-1 text-sm font-semibold text-gray-900">
                                —
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Registered Qty
                            </div>
                            <div id="summaryQuantity"
                                 class="mt-1 text-sm font-semibold text-gray-900">
                                —
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Ownership
                            </div>
                            <div id="summaryOwnership"
                                 class="mt-1 text-sm font-semibold text-gray-900">
                                —
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current Location Balances --}}
            <div id="locationCard"
                 class="hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            2. Current Location & Availability
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Available quantity already excludes pending or in-transit outgoing movements.
                        </p>
                    </div>
                </div>

                <div id="locationBalances"
                     class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                </div>
            </div>

            {{-- Movement --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    3. Movement Details
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div>
                        <label for="movement_type"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Movement Type <span class="text-red-600">*</span>
                        </label>

                        <select name="movement_type"
                                id="movement_type"
                                required
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">

                            @foreach ($movementTypes as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('movement_type', 'transfer') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Movement Status <span class="text-red-600">*</span>
                        </label>

                        <select name="status"
                                id="status"
                                required
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">

                            @foreach ($creationStatuses as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('status', 'received') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-1 text-xs text-gray-500">
                            Received / Complete records the movement immediately.
                            Pending Transfer uses Dispatch → Receive workflow.
                        </p>
                    </div>

                    {{-- Source --}}
                    <div>
                        <label for="from_project_id"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            From Location <span class="text-red-600">*</span>
                        </label>

                        <select name="from_project_id"
                                id="from_project_id"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">

                            <option value="">
                                Company Yard / Unallocated
                            </option>
                        </select>

                        <p id="sourceAvailability"
                           class="mt-1 text-xs text-gray-500">
                            Select equipment to see available source locations.
                        </p>
                    </div>

                    {{-- Destination --}}
                    <div>
                        <label for="to_project_id"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            To Location <span class="text-red-600">*</span>
                        </label>

                        <select name="to_project_id"
                                id="to_project_id"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">

                            <option value="">
                                Company Yard / Unallocated
                            </option>

                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}"
                                    @selected(
                                        (string) old('to_project_id')
                                        === (string) $project->id
                                    )>
                                    {{ $project->project_name }}
                                    @if ($project->project_code)
                                        ({{ $project->project_code }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="quantity"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Quantity <span class="text-red-600">*</span>
                        </label>

                        <div class="flex">
                            <input type="number"
                                   name="quantity"
                                   id="quantity"
                                   value="{{ old('quantity', 1) }}"
                                   step="0.001"
                                   min="0.001"
                                   required
                                   class="min-w-0 flex-1 rounded-l-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">

                            <span id="quantityUnit"
                                  class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-4 text-sm text-gray-600">
                                Nos
                            </span>
                        </div>

                        <p id="quantityHelp"
                           class="mt-1 text-xs text-gray-500">
                            Select equipment first.
                        </p>
                    </div>

                    <div>
                        <label for="movement_date"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Movement Date <span class="text-red-600">*</span>
                        </label>

                        <input type="date"
                               name="movement_date"
                               id="movement_date"
                               value="{{ old('movement_date', now()->toDateString()) }}"
                               required
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div>
                        <label for="movement_time"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Movement Time
                        </label>

                        <input type="time"
                               name="movement_time"
                               id="movement_time"
                               value="{{ old('movement_time') }}"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div id="expectedReturnWrapper">
                        <label for="expected_return_date"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Expected Return Date
                        </label>

                        <input type="date"
                               name="expected_return_date"
                               id="expected_return_date"
                               value="{{ old('expected_return_date') }}"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>
                </div>
            </div>

            {{-- Transport --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">
                    4. Movement Reference & Transport
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Reference Number
                        </label>

                        <input type="text"
                               name="reference_number"
                               value="{{ old('reference_number') }}"
                               maxlength="100"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Challan Number
                        </label>

                        <input type="text"
                               name="challan_number"
                               value="{{ old('challan_number') }}"
                               maxlength="100"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Vehicle Number
                        </label>

                        <input type="text"
                               name="vehicle_number"
                               value="{{ old('vehicle_number') }}"
                               maxlength="50"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Driver Name
                        </label>

                        <input type="text"
                               name="driver_name"
                               value="{{ old('driver_name') }}"
                               maxlength="150"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Driver Mobile
                        </label>

                        <input type="text"
                               name="driver_mobile"
                               value="{{ old('driver_mobile') }}"
                               maxlength="30"
                               class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Remarks
                        </label>

                        <textarea name="remarks"
                                  rows="3"
                                  maxlength="5000"
                                  class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('machinery-equipment-allocations.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">
                    Save Movement
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const equipmentSelect = document.getElementById('machinery_equipment_id');
    const movementType = document.getElementById('movement_type');
    const fromSelect = document.getElementById('from_project_id');
    const toSelect = document.getElementById('to_project_id');
    const quantityInput = document.getElementById('quantity');

    const equipmentSummary = document.getElementById('equipmentSummary');
    const locationCard = document.getElementById('locationCard');
    const locationBalances = document.getElementById('locationBalances');

    const summaryEquipment = document.getElementById('summaryEquipment');
    const summaryTracking = document.getElementById('summaryTracking');
    const summaryQuantity = document.getElementById('summaryQuantity');
    const summaryOwnership = document.getElementById('summaryOwnership');

    const quantityUnit = document.getElementById('quantityUnit');
    const quantityHelp = document.getElementById('quantityHelp');
    const sourceAvailability = document.getElementById('sourceAvailability');
    const expectedReturnWrapper = document.getElementById('expectedReturnWrapper');

    let equipmentData = null;
    let locations = [];

    const oldFromProjectId = @json(old('from_project_id'));
    const oldToProjectId = @json(old('to_project_id'));

    function formatQuantity(value) {
        const number = Number(value || 0);

        return number.toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3
        });
    }

    function labelize(value) {
        if (!value) {
            return '—';
        }

        return value
            .replaceAll('_', ' ')
            .replace(/\b\w/g, character => character.toUpperCase());
    }

    function resetEquipmentDisplay() {
        equipmentData = null;
        locations = [];

        equipmentSummary.classList.add('hidden');
        locationCard.classList.add('hidden');

        fromSelect.innerHTML =
            '<option value="">Company Yard / Unallocated</option>';

        sourceAvailability.textContent =
            'Select equipment to see available source locations.';

        quantityInput.readOnly = false;
        quantityInput.removeAttribute('max');

        quantityHelp.textContent = 'Select equipment first.';
        quantityUnit.textContent = 'Nos';
    }

    function renderLocations() {
        locationBalances.innerHTML = '';

        locations.forEach(location => {
            const card = document.createElement('div');

            card.className =
                'rounded-lg border border-gray-200 bg-gray-50 p-3';

            const title = document.createElement('div');
            title.className = 'font-semibold text-gray-900';
            title.textContent = location.project_name;

            const confirmed = document.createElement('div');
            confirmed.className = 'mt-2 text-xs text-gray-500';
            confirmed.textContent =
                'Confirmed: ' +
                formatQuantity(location.confirmed_quantity) +
                ' ' +
                equipmentData.unit;

            const available = document.createElement('div');
            available.className = 'mt-1 text-sm font-semibold text-gray-800';
            available.textContent =
                'Available: ' +
                formatQuantity(location.available_quantity) +
                ' ' +
                equipmentData.unit;

            card.appendChild(title);
            card.appendChild(confirmed);
            card.appendChild(available);

            locationBalances.appendChild(card);
        });
    }

    function populateSourceLocations() {
        const currentValue = fromSelect.value;

        fromSelect.innerHTML = '';

        locations
            .filter(location => Number(location.available_quantity) > 0)
            .forEach(location => {
                const option = document.createElement('option');

                option.value =
                    location.project_id === null
                        ? ''
                        : String(location.project_id);

                option.textContent =
                    location.project_name +
                    ' — ' +
                    formatQuantity(location.available_quantity) +
                    ' ' +
                    equipmentData.unit +
                    ' available';

                option.dataset.available =
                    location.available_quantity;

                fromSelect.appendChild(option);
            });

        if (oldFromProjectId !== null && oldFromProjectId !== '') {
            fromSelect.value = String(oldFromProjectId);
        } else if (
            currentValue !== null &&
            currentValue !== '' &&
            [...fromSelect.options].some(
                option => option.value === currentValue
            )
        ) {
            fromSelect.value = currentValue;
        }

        updateSourceAvailability();
    }

    function updateSourceAvailability() {
        const option =
            fromSelect.options[fromSelect.selectedIndex];

        if (!option || !equipmentData) {
            return;
        }

        const available =
            Number(option.dataset.available || 0);

        sourceAvailability.textContent =
            'Available at source: ' +
            formatQuantity(available) +
            ' ' +
            equipmentData.unit;

        if (equipmentData.tracking_mode === 'individual') {
            quantityInput.value = 1;
            quantityInput.readOnly = true;
            quantityInput.max = 1;

            quantityHelp.textContent =
                'Individual equipment always moves as quantity 1.';
        } else {
            quantityInput.readOnly = false;
            quantityInput.max = available;

            quantityHelp.textContent =
                'Maximum available from this source: ' +
                formatQuantity(available) +
                ' ' +
                equipmentData.unit;
        }
    }

    function applyMovementRules() {
        const type = movementType.value;

        /*
         * Initial Allocation:
         * Company Yard -> Project
         */
        if (type === 'initial_allocation') {
            fromSelect.value = '';
            fromSelect.disabled = true;

            toSelect.disabled = false;

            expectedReturnWrapper.classList.add('hidden');
        }

        /*
         * Return:
         * Project -> Company Yard
         */
        else if (type === 'return') {
            fromSelect.disabled = false;

            toSelect.value = '';
            toSelect.disabled = true;

            expectedReturnWrapper.classList.add('hidden');
        }

        /*
         * Temporary transfer:
         * Project/Yard -> Project
         */
        else if (type === 'temporary_transfer') {
            fromSelect.disabled = false;
            toSelect.disabled = false;

            expectedReturnWrapper.classList.remove('hidden');
        }

        /*
         * Normal project transfer.
         */
        else {
            fromSelect.disabled = false;
            toSelect.disabled = false;

            expectedReturnWrapper.classList.add('hidden');
        }

        /*
         * Disabled selects are not submitted by browsers.
         * Hidden inputs below preserve required null/project values.
         */
        syncDisabledLocationInputs();
    }

    function syncDisabledLocationInputs() {
        document
            .querySelectorAll('.movement-location-hidden')
            .forEach(element => element.remove());

        if (fromSelect.disabled) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'from_project_id';
            input.value = fromSelect.value;
            input.className = 'movement-location-hidden';

            fromSelect.parentNode.appendChild(input);
        }

        if (toSelect.disabled) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'to_project_id';
            input.value = toSelect.value;
            input.className = 'movement-location-hidden';

            toSelect.parentNode.appendChild(input);
        }
    }

    async function loadEquipment() {
        const equipmentId = equipmentSelect.value;

        if (!equipmentId) {
            resetEquipmentDisplay();
            return;
        }

        equipmentSummary.classList.add('hidden');
        locationCard.classList.add('hidden');

        sourceAvailability.textContent =
            'Loading equipment availability...';

        try {
            const urlTemplate = @json(
                route(
                    'machinery-equipment-allocations.equipment-availability',
                    ['machineryEquipment' => '__EQUIPMENT__']
                )
            );

            const url = urlTemplate.replace(
                '__EQUIPMENT__',
                equipmentId
            );

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(
                    'Unable to load equipment availability.'
                );
            }

            const data = await response.json();

            equipmentData = data.equipment;
            locations = data.locations || [];

            summaryEquipment.textContent =
                equipmentData.equipment_code +
                ' — ' +
                equipmentData.display_name;

            summaryTracking.textContent =
                labelize(equipmentData.tracking_mode);

            summaryQuantity.textContent =
                formatQuantity(
                    equipmentData.registered_quantity
                ) +
                ' ' +
                equipmentData.unit;

            summaryOwnership.textContent =
                labelize(equipmentData.ownership_type);

            quantityUnit.textContent =
                equipmentData.unit || 'Nos';

            equipmentSummary.classList.remove('hidden');
            locationCard.classList.remove('hidden');

            renderLocations();
            populateSourceLocations();

            if (
                oldToProjectId !== null &&
                oldToProjectId !== ''
            ) {
                toSelect.value = String(oldToProjectId);
            }

            applyMovementRules();
            updateSourceAvailability();

        } catch (error) {
            resetEquipmentDisplay();

            alert(
                error.message ||
                'Unable to load equipment availability.'
            );
        }
    }

    equipmentSelect.addEventListener(
        'change',
        loadEquipment
    );

    fromSelect.addEventListener(
        'change',
        function () {
            updateSourceAvailability();
            syncDisabledLocationInputs();
        }
    );

    toSelect.addEventListener(
        'change',
        syncDisabledLocationInputs
    );

    movementType.addEventListener(
        'change',
        function () {
            applyMovementRules();
            updateSourceAvailability();
        }
    );

    document
        .getElementById('movementForm')
        .addEventListener('submit', function () {
            /*
             * Ensure disabled location values have their hidden
             * equivalents immediately before submission.
             */
            syncDisabledLocationInputs();
        });

    if (equipmentSelect.value) {
        loadEquipment();
    } else {
        resetEquipmentDisplay();
    }

    applyMovementRules();
});
</script>
@endsection