@php
    use App\Models\MachineryDailyUsage;

    $editing = isset($usage) && $usage;

    $value = function ($field, $default = null) use ($editing, $usage) {
        return old(
            $field,
            $editing ? data_get($usage, $field) : $default
        );
    };

    $selectedProject = $value(
        'project_id',
        $selectedProjectId ?? null
    );

    $selectedEquipment = $value(
        'machinery_equipment_id'
    );
@endphp

<div class="space-y-6">

    {{-- ============================================================
         1. PROJECT / DATE
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                1. Site & Reporting Date
            </h2>
            <p class="mt-1 text-xs text-gray-500">
                Select the project first. Only equipment physically allocated to that site will be available.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">

            <div>
                <label for="project_id"
                       class="block text-sm font-medium text-gray-700">
                    Project <span class="text-red-600">*</span>
                </label>

                <select name="project_id"
                        id="project_id"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Project</option>

                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"
                            @selected((string) $selectedProject === (string) $project->id)>
                            {{ $project->project_code }}
                            — {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="usage_date"
                       class="block text-sm font-medium text-gray-700">
                    Reporting Date <span class="text-red-600">*</span>
                </label>

                <input type="date"
                       name="usage_date"
                       id="usage_date"
                       required
                       value="{{ old(
    'usage_date',
    $editing && $usage->usage_date
        ? $usage->usage_date->format('Y-m-d')
        : now()->format('Y-m-d')
) }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="shift"
                       class="block text-sm font-medium text-gray-700">
                    Shift <span class="text-red-600">*</span>
                </label>

                <select name="shift"
                        id="shift"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    @foreach (MachineryDailyUsage::shifts() as $key => $label)
                        <option value="{{ $key }}"
                            @selected($value('shift', MachineryDailyUsage::SHIFT_GENERAL) === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </section>

    {{-- ============================================================
         2. EQUIPMENT
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                2. Machinery / Equipment
            </h2>
        </div>

        <div class="p-5">

            <div>
                <label for="machinery_equipment_id"
                       class="block text-sm font-medium text-gray-700">
                    Equipment <span class="text-red-600">*</span>
                </label>

                <select name="machinery_equipment_id"
                        id="machinery_equipment_id"
                        required
                        {{ !$selectedProject ? 'disabled' : '' }}
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">
                        {{ $selectedProject
                            ? 'Select Equipment'
                            : 'Select Project First' }}
                    </option>

                    @foreach (($availableEquipment ?? collect()) as $equipment)
                        <option value="{{ $equipment->id }}"
                            @selected((string) $selectedEquipment === (string) $equipment->id)>
                            {{ $equipment->equipment_code }}
                            — {{ $equipment->equipment_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="equipment-info"
                 class="mt-4 hidden rounded-lg border border-blue-200 bg-blue-50 p-4">

                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">

                    <div>
                        <div class="text-xs text-gray-500">Category</div>
                        <div id="info-category"
                             class="mt-1 text-sm font-semibold text-gray-900">—</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Ownership</div>
                        <div id="info-ownership"
                             class="mt-1 text-sm font-semibold text-gray-900">—</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Tracking</div>
                        <div id="info-tracking"
                             class="mt-1 text-sm font-semibold text-gray-900">—</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">At Site</div>
                        <div id="info-available"
                             class="mt-1 text-sm font-semibold text-gray-900">—</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Meter</div>
                        <div id="info-meter"
                             class="mt-1 text-sm font-semibold text-gray-900">—</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Fuel / Power</div>
                        <div id="info-fuel"
                             class="mt-1 text-sm font-semibold text-gray-900">—</div>
                    </div>

                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="quantity_used"
                           class="block text-sm font-medium text-gray-700">
                        Quantity Used <span class="text-red-600">*</span>
                    </label>

                    <div class="mt-1 flex rounded-lg shadow-sm">
                        <input type="number"
                               step="0.001"
                               min="0.001"
                               name="quantity_used"
                               id="quantity_used"
                               value="{{ $value('quantity_used', 1) }}"
                               required
                               class="block w-full rounded-l-lg border-gray-300">

                        <div id="quantity-unit"
                             class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-4 text-sm text-gray-600">
                            —
                        </div>
                    </div>

                    <p id="quantity-help"
                       class="mt-1 text-xs text-gray-500">
                        Select equipment to view site quantity.
                    </p>
                </div>

                <div>
                    <label for="working_condition"
                           class="block text-sm font-medium text-gray-700">
                        Working Condition <span class="text-red-600">*</span>
                    </label>

                    <select name="working_condition"
                            id="working_condition"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                        @foreach (MachineryDailyUsage::workingConditions() as $key => $label)
                            <option value="{{ $key }}"
                                @selected($value('working_condition', MachineryDailyUsage::CONDITION_WORKING) === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
    </section>

    {{-- ============================================================
         3. TIME / OPERATING HOURS
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                3. Usage Time & Operating Hours
            </h2>
            <p class="mt-1 text-xs text-gray-500">
                Site duration and actual operating hours are recorded separately.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2 lg:grid-cols-4">

            <div>
                <label for="start_time"
                       class="block text-sm font-medium text-gray-700">
                    Start Time
                </label>

                <input
    type="time"
    name="start_time"
    id="start_time"
    value="{{ old(
        'start_time',
        $editing && $usage->start_time
            ? \Illuminate\Support\Str::substr($usage->start_time, 0, 5)
            : ''
    ) }}"
    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
>
            </div>

            <div>
                <label for="end_time"
                       class="block text-sm font-medium text-gray-700">
                    End Time
                </label>

                <input
    type="time"
    name="end_time"
    id="end_time"
    value="{{ old(
        'end_time',
        $editing && $usage->end_time
            ? \Illuminate\Support\Str::substr($usage->end_time, 0, 5)
            : ''
    ) }}"
    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Site Duration
                </label>

                <div id="site-hours-display"
                     class="mt-1 flex min-h-[42px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-gray-700">
                    —
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Final Operating Hours
                </label>

                <div id="final-hours-display"
                     class="mt-1 flex min-h-[42px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-gray-700">
                    —
                </div>
            </div>

        </div>

        {{-- Metered equipment --}}
        <div id="meter-section"
             class="hidden border-t border-gray-100 p-5">

            <div class="mb-3">
                <h3 class="text-sm font-semibold text-gray-900">
                    Meter Readings
                </h3>

                <p id="meter-help"
                   class="mt-1 text-xs text-gray-500"></p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                <div>
                    <label for="opening_meter_reading"
                           class="block text-sm font-medium text-gray-700">
                        Opening Reading
                    </label>

                    <input type="number"
                           step="0.001"
                           min="0"
                           name="opening_meter_reading"
                           id="opening_meter_reading"
                           value="{{ $value('opening_meter_reading') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label for="closing_meter_reading"
                           class="block text-sm font-medium text-gray-700">
                        Closing Reading
                    </label>

                    <input type="number"
                           step="0.001"
                           min="0"
                           name="closing_meter_reading"
                           id="closing_meter_reading"
                           value="{{ $value('closing_meter_reading') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Meter Difference
                    </label>

                    <div id="meter-difference"
                         class="mt-1 flex min-h-[42px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-gray-700">
                        —
                    </div>
                </div>

            </div>
        </div>

        <div class="border-t border-gray-100 p-5">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                <div>
                    <label for="manual_operating_hours"
                           class="block text-sm font-medium text-gray-700">
                        Manual Operating Hours
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           max="24"
                           name="manual_operating_hours"
                           id="manual_operating_hours"
                           value="{{ $value('manual_operating_hours') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">

                    <p id="manual-hours-help"
                       class="mt-1 text-xs text-gray-500">
                        Enter actual operating hours for non-hour-meter equipment.
                    </p>
                </div>

                <div>
                    <label for="idle_hours"
                           class="block text-sm font-medium text-gray-700">
                        Idle Hours
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           max="24"
                           name="idle_hours"
                           id="idle_hours"
                           value="{{ $value('idle_hours', 0) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label for="breakdown_hours"
                           class="block text-sm font-medium text-gray-700">
                        Breakdown Hours
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           max="24"
                           name="breakdown_hours"
                           id="breakdown_hours"
                           value="{{ $value('breakdown_hours', 0) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                </div>

            </div>

            <div id="manual-reason-wrapper"
                 class="mt-5 hidden">

                <label for="manual_hours_reason"
                       class="block text-sm font-medium text-gray-700">
                    Manual Hours / Override Reason
                </label>

                <textarea name="manual_hours_reason"
                          id="manual_hours_reason"
                          rows="2"
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm"
                          placeholder="Explain why manual operating hours are being used...">{{ $value('manual_hours_reason') }}</textarea>
            </div>
        </div>
    </section>

    {{-- ============================================================
         4. WORK EXECUTION
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                4. Work Performed
            </h2>
            <p class="mt-1 text-xs text-gray-500">
                Link the machinery usage to the actual work when applicable.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

            <div>
                <label for="work_activity_id"
                       class="block text-sm font-medium text-gray-700">
                    Work Activity
                </label>

                <select name="work_activity_id"
                        id="work_activity_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Activity</option>

                    @foreach ($workActivities as $activity)
                        <option value="{{ $activity->id }}"
                            @selected((string) $value('work_activity_id') === (string) $activity->id)>
                            {{ $activity->code }}
                            — {{ $activity->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="work_done_item_id"
                       class="block text-sm font-medium text-gray-700">
                    Work Done Entry
                </label>

                <select name="work_done_item_id"
                        id="work_done_item_id"
                        disabled
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">
                        Select project/date first
                    </option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="work_description"
                       class="block text-sm font-medium text-gray-700">
                    Work Description
                </label>

                <textarea name="work_description"
                          id="work_description"
                          rows="3"
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm"
                          placeholder="Example: Excavation for footing F1–F4, soil loading and shifting...">{{ $value('work_description') }}</textarea>
            </div>

        </div>
    </section>

    {{-- ============================================================
         5. EXACT SITE LOCATION
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                5. Work Location
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2 lg:grid-cols-5">

            <div>
                <label for="project_block_id"
                       class="block text-sm font-medium text-gray-700">
                    Block
                </label>

                <select name="project_block_id"
                        id="project_block_id"
                        disabled
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Block</option>
                </select>
            </div>

            <div>
                <label for="project_floor_id"
                       class="block text-sm font-medium text-gray-700">
                    Floor
                </label>

                <select name="project_floor_id"
                        id="project_floor_id"
                        disabled
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Floor</option>
                </select>
            </div>

            <div>
                <label for="project_unit_id"
                       class="block text-sm font-medium text-gray-700">
                    Unit
                </label>

                <select name="project_unit_id"
                        id="project_unit_id"
                        disabled
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Unit</option>
                </select>
            </div>

            <div>
                <label for="project_room_id"
                       class="block text-sm font-medium text-gray-700">
                    Room
                </label>

                <select name="project_room_id"
                        id="project_room_id"
                        disabled
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Room</option>
                </select>
            </div>

            <div>
                <label for="project_subspace_id"
                       class="block text-sm font-medium text-gray-700">
                    Sub-space
                </label>

                <select name="project_subspace_id"
                        id="project_subspace_id"
                        disabled
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Sub-space</option>
                </select>
            </div>

        </div>
    </section>

    {{-- ============================================================
         6. OPERATOR / FUEL
    ============================================================ --}}
    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">
                6. Operator & Fuel / Energy
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2 lg:grid-cols-3">

            <div>
                <label for="operator_user_id"
                       class="block text-sm font-medium text-gray-700">
                    ERP Operator
                </label>

                <select name="operator_user_id"
                        id="operator_user_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">External / Not an ERP User</option>

                    @foreach ($operators as $operator)
                        <option value="{{ $operator->id }}"
                            @selected((string) $value('operator_user_id') === (string) $operator->id)>
                            {{ $operator->employee_code }}
                            — {{ $operator->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="operator_name"
                       class="block text-sm font-medium text-gray-700">
                    Operator / Driver Name
                </label>

                <input type="text"
                       name="operator_name"
                       id="operator_name"
                       maxlength="150"
                       value="{{ $value('operator_name') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="operator_mobile"
                       class="block text-sm font-medium text-gray-700">
                    Operator Mobile
                </label>

                <input type="text"
                       name="operator_mobile"
                       id="operator_mobile"
                       maxlength="30"
                       value="{{ $value('operator_mobile') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="fuel_energy_quantity"
                       class="block text-sm font-medium text-gray-700">
                    Fuel / Energy Used
                </label>

                <input type="number"
                       step="0.001"
                       min="0"
                       name="fuel_energy_quantity"
                       id="fuel_energy_quantity"
                       value="{{ $value('fuel_energy_quantity') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="fuel_energy_unit"
                       class="block text-sm font-medium text-gray-700">
                    Fuel / Energy Unit
                </label>

                <select name="fuel_energy_unit"
                        id="fuel_energy_unit"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Select Unit</option>
                    <option value="Litre" @selected($value('fuel_energy_unit') === 'Litre')>Litre</option>
                    <option value="kWh" @selected($value('fuel_energy_unit') === 'kWh')>kWh</option>
                    <option value="Kg" @selected($value('fuel_energy_unit') === 'Kg')>Kg</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="remarks"
                       class="block text-sm font-medium text-gray-700">
                    Remarks
                </label>

                <textarea name="remarks"
                          id="remarks"
                          rows="3"
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">{{ $value('remarks') }}</textarea>
            </div>

        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const project = document.getElementById('project_id');
    const equipment = document.getElementById('machinery_equipment_id');

    const usageDate = document.getElementById('usage_date');
    const activity = document.getElementById('work_activity_id');
    const workDone = document.getElementById('work_done_item_id');

    const block = document.getElementById('project_block_id');
    const floor = document.getElementById('project_floor_id');
    const unit = document.getElementById('project_unit_id');
    const room = document.getElementById('project_room_id');
    const subspace = document.getElementById('project_subspace_id');

    const quantity = document.getElementById('quantity_used');

    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');

    const openingMeter = document.getElementById('opening_meter_reading');
    const closingMeter = document.getElementById('closing_meter_reading');

    const manualHours = document.getElementById('manual_operating_hours');

    let currentEquipment = null;

    const oldValues = {
        equipment: @json((string) $selectedEquipment),
        workDone: @json((string) $value('work_done_item_id')),
        block: @json((string) $value('project_block_id')),
        floor: @json((string) $value('project_floor_id')),
        unit: @json((string) $value('project_unit_id')),
        room: @json((string) $value('project_room_id')),
        subspace: @json((string) $value('project_subspace_id')),
    };

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }

    function addOption(select, value, label, selectedValue = '') {
        const option = document.createElement('option');

        option.value = value;
        option.textContent = label;

        if (String(value) === String(selectedValue)) {
            option.selected = true;
        }

        select.appendChild(option);
    }

    async function getJson(url) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message || 'Unable to load information.'
            );
        }

        return data;
    }

    function route(path) {
        return `${window.location.origin}/${path}`;
    }

    async function loadEquipment(selected = '') {
        resetSelect(equipment, 'Loading equipment...');

        document.getElementById('equipment-info')
            .classList.add('hidden');

        currentEquipment = null;

        if (!project.value) {
            resetSelect(equipment, 'Select Project First');
            return;
        }

        try {
            const data = await getJson(
                route(
                    `machinery-daily-usages/project/${project.value}/equipment`
                )
            );

            equipment.innerHTML =
                '<option value="">Select Equipment</option>';

            data.equipment.forEach(item => {
                addOption(
                    equipment,
                    item.id,
                    `${item.equipment_code} — ${item.equipment_name}`,
                    selected
                );
            });

            equipment.disabled = false;

            if (selected) {
                await loadEquipmentDetails();
            }
        } catch (error) {
            resetSelect(
                equipment,
                'Unable to load equipment'
            );

            console.error(error);
        }
    }

    async function loadEquipmentDetails() {
        if (!project.value || !equipment.value) {
            document.getElementById('equipment-info')
                .classList.add('hidden');

            currentEquipment = null;
            return;
        }

        try {
            currentEquipment = await getJson(
                route(
                    `machinery-daily-usages/project/${project.value}/equipment/${equipment.value}`
                )
            );

            document.getElementById('info-category').textContent =
                currentEquipment.category || '—';

            document.getElementById('info-ownership').textContent =
                currentEquipment.ownership_type || '—';

            document.getElementById('info-tracking').textContent =
                currentEquipment.tracking_mode || '—';

            document.getElementById('info-available').textContent =
                `${currentEquipment.confirmed_quantity} ${currentEquipment.unit || ''}`;

            document.getElementById('info-meter').textContent =
                currentEquipment.meter_type || 'None';

            document.getElementById('info-fuel').textContent =
                currentEquipment.fuel_type || 'None';

            document.getElementById('quantity-unit').textContent =
                currentEquipment.unit || '—';

            if (currentEquipment.tracking_mode === 'individual') {
                quantity.value = 1;
                quantity.readOnly = true;

                document.getElementById('quantity-help').textContent =
                    'Individual equipment is always reported as quantity 1.';
            } else {
                quantity.readOnly = false;
                quantity.max = currentEquipment.confirmed_quantity;

                document.getElementById('quantity-help').textContent =
                    `Maximum at this site: ${currentEquipment.confirmed_quantity} ${currentEquipment.unit || ''}.`;
            }

            configureMeter();

            document.getElementById('equipment-info')
                .classList.remove('hidden');

        } catch (error) {
            currentEquipment = null;

            document.getElementById('equipment-info')
                .classList.add('hidden');

            console.error(error);
        }
    }

    function configureMeter() {
        const meterSection =
            document.getElementById('meter-section');

        const meterHelp =
            document.getElementById('meter-help');

        const manualHelp =
            document.getElementById('manual-hours-help');

        if (!currentEquipment) {
            meterSection.classList.add('hidden');
            return;
        }

        if (currentEquipment.meter_type === 'hour_meter') {
            meterSection.classList.remove('hidden');

            meterHelp.textContent =
                'Hour meter: closing minus opening will calculate actual meter operating hours.';

            manualHelp.textContent =
                'Only enter manual hours when overriding the hour meter. A reason will be required.';

            if (
                !openingMeter.value &&
                currentEquipment.current_meter_reading !== null
            ) {
                openingMeter.value =
                    currentEquipment.current_meter_reading;
            }
        } else if (
            currentEquipment.meter_type === 'odometer'
        ) {
            meterSection.classList.remove('hidden');

            meterHelp.textContent =
                'Odometer difference represents distance and is not treated as operating hours.';

            manualHelp.textContent =
                'Enter actual operating hours manually.';
        } else {
            meterSection.classList.add('hidden');

            manualHelp.textContent =
                'Enter actual operating hours for this equipment.';
        }

        calculateMeterDifference();
        calculateFinalHours();
    }

    function calculateSiteDuration() {
        if (!startTime.value || !endTime.value) {
            document.getElementById('site-hours-display')
                .textContent = '—';

            return null;
        }

        const [sh, sm] =
            startTime.value.split(':').map(Number);

        const [eh, em] =
            endTime.value.split(':').map(Number);

        let start = sh * 60 + sm;
        let end = eh * 60 + em;

        if (end <= start) {
            end += 24 * 60;
        }

        const hours = (end - start) / 60;

        document.getElementById('site-hours-display')
            .textContent =
                `${hours.toFixed(2)} hrs`;

        return hours;
    }

    function calculateMeterDifference() {
        const display =
            document.getElementById('meter-difference');

        if (
            openingMeter.value === '' ||
            closingMeter.value === ''
        ) {
            display.textContent = '—';
            return null;
        }

        const opening = Number(openingMeter.value);
        const closing = Number(closingMeter.value);

        if (closing < opening) {
            display.textContent = 'Invalid';
            return null;
        }

        const difference = closing - opening;

        if (
            currentEquipment &&
            currentEquipment.meter_type === 'hour_meter'
        ) {
            display.textContent =
                `${difference.toFixed(2)} hrs`;
        } else {
            display.textContent =
                difference.toFixed(3);
        }

        return difference;
    }

    function calculateFinalHours() {
        let finalHours = null;

        const manual =
            manualHours.value !== ''
                ? Number(manualHours.value)
                : null;

        if (
            currentEquipment &&
            currentEquipment.meter_type === 'hour_meter'
        ) {
            const meterDifference =
                calculateMeterDifference();

            finalHours =
                manual !== null
                    ? manual
                    : meterDifference;

            const reasonWrapper =
                document.getElementById(
                    'manual-reason-wrapper'
                );

            if (manual !== null) {
                reasonWrapper.classList.remove('hidden');
            } else {
                reasonWrapper.classList.add('hidden');
            }
        } else {
            finalHours = manual;

            document.getElementById(
                'manual-reason-wrapper'
            ).classList.add('hidden');
        }

        document.getElementById(
            'final-hours-display'
        ).textContent =
            finalHours !== null && !Number.isNaN(finalHours)
                ? `${finalHours.toFixed(2)} hrs`
                : '—';
    }

    async function loadBlocks(selected = '') {
        resetSelect(block, 'Loading...');

        resetSelect(floor, 'Select Floor');
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');

        if (!project.value) {
            resetSelect(block, 'Select Block');
            return;
        }

        try {
            const data = await getJson(
                route(
                    `machinery-daily-usages/project/${project.value}/structure`
                )
            );

            block.innerHTML =
                '<option value="">Select Block</option>';

            data.blocks.forEach(item => {
                addOption(
                    block,
                    item.id,
                    item.code
                        ? `${item.code} — ${item.name}`
                        : item.name,
                    selected
                );
            });

            block.disabled = false;

            if (selected) {
                await loadFloors(oldValues.floor);
            }
        } catch (error) {
            console.error(error);
        }
    }

    async function loadFloors(selected = '') {
        resetSelect(floor, 'Select Floor');
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');

        if (!project.value || !block.value) {
            return;
        }

        const data = await getJson(
            route(
                `machinery-daily-usages/project/${project.value}/blocks/${block.value}/floors`
            )
        );

        data.floors.forEach(item => {
            addOption(
                floor,
                item.id,
                item.name,
                selected
            );
        });

        floor.disabled = false;

        if (selected) {
            await loadUnits(oldValues.unit);
        }
    }

    async function loadUnits(selected = '') {
        resetSelect(unit, 'Select Unit');
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');

        if (!project.value || !floor.value) {
            return;
        }

        const data = await getJson(
            route(
                `machinery-daily-usages/project/${project.value}/floors/${floor.value}/units`
            )
        );

        data.units.forEach(item => {
            addOption(
                unit,
                item.id,
                item.name,
                selected
            );
        });

        unit.disabled = false;

        if (selected) {
            await loadRooms(oldValues.room);
        }
    }

    async function loadRooms(selected = '') {
        resetSelect(room, 'Select Room');
        resetSelect(subspace, 'Select Sub-space');

        if (!project.value || !unit.value) {
            return;
        }

        const data = await getJson(
            route(
                `machinery-daily-usages/project/${project.value}/units/${unit.value}/rooms`
            )
        );

        data.rooms.forEach(item => {
            addOption(
                room,
                item.id,
                item.name || item.room_type,
                selected
            );
        });

        room.disabled = false;

        if (selected) {
            await loadSubspaces(oldValues.subspace);
        }
    }

    async function loadSubspaces(selected = '') {
        resetSelect(subspace, 'Select Sub-space');

        if (!project.value || !room.value) {
            return;
        }

        const data = await getJson(
            route(
                `machinery-daily-usages/project/${project.value}/rooms/${room.value}/subspaces`
            )
        );

        data.subspaces.forEach(item => {
            addOption(
                subspace,
                item.id,
                item.name,
                selected
            );
        });

        subspace.disabled = false;
    }

    async function loadWorkDone(selected = '') {
        resetSelect(
            workDone,
            'Loading Work Done entries...'
        );

        if (!project.value) {
            resetSelect(
                workDone,
                'Select project/date first'
            );
            return;
        }

        let url =
            `machinery-daily-usages/project/${project.value}/work-done-items`;

        const params = new URLSearchParams();

        if (usageDate.value) {
            params.set(
                'usage_date',
                usageDate.value
            );
        }

        if (activity.value) {
            params.set(
                'work_activity_id',
                activity.value
            );
        }

        if ([...params].length) {
            url += `?${params.toString()}`;
        }

        try {
            const data = await getJson(route(url));

            workDone.innerHTML =
                '<option value="">No Work Done Link</option>';

            data.items.forEach(item => {
                const activityLabel =
                    item.activity_name ||
                    item.activity_code ||
                    `Work Item #${item.id}`;

                const quantityLabel =
                    item.quantity_completed !== null
                        ? ` | ${item.quantity_completed} ${item.unit || ''}`
                        : '';

                addOption(
                    workDone,
                    item.id,
                    `${item.work_date} — ${activityLabel}${quantityLabel}`,
                    selected
                );
            });

            workDone.disabled = false;
        } catch (error) {
            resetSelect(
                workDone,
                'Unable to load Work Done entries'
            );

            console.error(error);
        }
    }

    project.addEventListener('change', async () => {
        oldValues.equipment = '';
        oldValues.block = '';
        oldValues.floor = '';
        oldValues.unit = '';
        oldValues.room = '';
        oldValues.subspace = '';
        oldValues.workDone = '';

        await loadEquipment();
        await loadBlocks();
        await loadWorkDone();
    });

    equipment.addEventListener(
        'change',
        loadEquipmentDetails
    );

    block.addEventListener(
        'change',
        () => loadFloors()
    );

    floor.addEventListener(
        'change',
        () => loadUnits()
    );

    unit.addEventListener(
        'change',
        () => loadRooms()
    );

    room.addEventListener(
        'change',
        () => loadSubspaces()
    );

    usageDate.addEventListener(
        'change',
        () => loadWorkDone()
    );

    activity.addEventListener(
        'change',
        () => loadWorkDone()
    );

    startTime.addEventListener(
        'change',
        calculateSiteDuration
    );

    endTime.addEventListener(
        'change',
        calculateSiteDuration
    );

    openingMeter.addEventListener(
        'input',
        () => {
            calculateMeterDifference();
            calculateFinalHours();
        }
    );

    closingMeter.addEventListener(
        'input',
        () => {
            calculateMeterDifference();
            calculateFinalHours();
        }
    );

    manualHours.addEventListener(
        'input',
        calculateFinalHours
    );

    /*
     * Initial page load / validation return / edit.
     */
    if (project.value) {
        loadEquipment(oldValues.equipment);
        loadBlocks(oldValues.block);
        loadWorkDone(oldValues.workDone);
    }

    calculateSiteDuration();
    calculateFinalHours();
});
</script>