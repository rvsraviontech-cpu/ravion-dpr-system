@php
    $equipment = $machineryEquipment ?? null;

    $selectedToolId = old('machinery_tool_id', $equipment?->machinery_tool_id);
    $selectedOwnership = old('ownership_type', $equipment?->ownership_type ?? 'company_owned');
    $selectedStatus = old('status', $equipment?->status ?? 'available');
    $selectedQuantity = old('quantity', $equipment?->quantity ?? 1);
@endphp

<div
    x-data="{
        ownership: @js($selectedOwnership),
        selectedToolId: @js((string) ($selectedToolId ?? '')),
        trackingMode: @js($equipment?->tracking_mode ?? ''),
        unit: @js($equipment?->unit ?? 'Nos'),
        meterType: @js($equipment?->meter_type ?? ''),
        fuelType: @js($equipment?->fuel_type ?? ''),
        quantity: @js((string) $selectedQuantity),

        tools: @js(
            $machineryTools->mapWithKeys(fn ($tool) => [
                (string) $tool->id => [
                    'tracking_mode' => $tool->tracking_mode,
                    'unit' => $tool->unit,
                    'meter_type' => $tool->meter_type,
                    'fuel_type' => $tool->fuel_type,
                ]
            ])->all()
        ),

        updateToolDefaults() {
            const tool = this.tools[this.selectedToolId];

            if (!tool) {
                return;
            }

            this.trackingMode = tool.tracking_mode || 'individual';
            this.unit = tool.unit || 'Nos';
            this.meterType = tool.meter_type || 'none';
            this.fuelType = tool.fuel_type || 'none';

            if (this.trackingMode === 'individual') {
                this.quantity = '1';
            }
        }
    }"
    x-init="
        if (selectedToolId && !trackingMode) {
            updateToolDefaults();
        }
    "
    class="space-y-6"
>
    {{-- Equipment Identification --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Equipment Identification
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Select the equipment type and identify this physical equipment or pooled equipment group.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Equipment Type <span class="text-red-500">*</span>
                </label>

                <select
                    name="machinery_tool_id"
                    x-model="selectedToolId"
                    @change="updateToolDefaults()"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select Equipment Type</option>

                    @foreach($machineryTools->groupBy('category') as $category => $tools)
                        <optgroup label="{{ $category }}">
                            @foreach($tools as $tool)
                                <option
                                    value="{{ $tool->id }}"
                                    @selected((string) $selectedToolId === (string) $tool->id)
                                >
                                    {{ $tool->machine_name }} ({{ $tool->code }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                @error('machinery_tool_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Equipment Code <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="equipment_code"
                    value="{{ old('equipment_code', $equipment?->equipment_code) }}"
                    required
                    maxlength="50"
                    placeholder="e.g. RV-EXC-001"
                    class="w-full rounded-lg border-gray-300 uppercase focus:border-blue-500 focus:ring-blue-500"
                >

                @error('equipment_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Equipment Name / Site Name
                </label>

                <input
                    type="text"
                    name="equipment_name"
                    value="{{ old('equipment_name', $equipment?->equipment_name) }}"
                    maxlength="255"
                    placeholder="Optional custom name"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Leave blank to use the Equipment Type name.
                </p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Tracking Mode
                </label>

                <input
                    type="text"
                    :value="trackingMode === 'pooled' ? 'Pooled / Quantity Based' : (trackingMode === 'individual' ? 'Individual Equipment' : 'Select equipment type')"
                    readonly
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-700"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Quantity <span class="text-red-500">*</span>
                </label>

                <input
                    type="number"
                    name="quantity"
                    x-model="quantity"
                    :readonly="trackingMode === 'individual'"
                    step="0.001"
                    min="0.001"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-50"
                >

                <p
                    x-show="trackingMode === 'individual'"
                    class="mt-1 text-xs text-gray-500"
                >
                    Individual equipment is always registered as one physical machine.
                </p>

                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Unit
                </label>

                <input
                    type="text"
                    :value="unit || 'Nos'"
                    readonly
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-700"
                >
            </div>

        </div>
    </div>

    {{-- Ownership --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Ownership & Source
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Ownership determines the source information required for this equipment.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Ownership <span class="text-red-500">*</span>
                </label>

                <select
                    name="ownership_type"
                    x-model="ownership"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    @foreach($ownershipTypes as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($selectedOwnership === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @error('ownership_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="ownership === 'rented'" x-cloak>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Vendor <span class="text-red-500">*</span>
                </label>

                <select
                    name="vendor_id"
                    :required="ownership === 'rented'"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select Vendor</option>

                    @foreach($vendors as $vendor)
                        <option
                            value="{{ $vendor->id }}"
                            @selected((string) old('vendor_id', $equipment?->vendor_id) === (string) $vendor->id)
                        >
                            {{ $vendor->vendor_name }}
                            @if($vendor->vendor_code)
                                ({{ $vendor->vendor_code }})
                            @endif
                        </option>
                    @endforeach
                </select>

                @error('vendor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="ownership === 'contractor_provided'" x-cloak>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Contractor <span class="text-red-500">*</span>
                </label>

                <select
                    name="contractor_id"
                    :required="ownership === 'contractor_provided'"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select Contractor</option>

                    @foreach($contractors as $contractor)
                        <option
                            value="{{ $contractor->id }}"
                            @selected((string) old('contractor_id', $equipment?->contractor_id) === (string) $contractor->id)
                        >
                            {{ $contractor->contractor_name }}
                            @if($contractor->contractor_code)
                                ({{ $contractor->contractor_code }})
                            @endif
                        </option>
                    @endforeach
                </select>

                @error('contractor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="ownership === 'company_owned'" x-cloak>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Asset Number
                </label>

                <input
                    type="text"
                    name="asset_number"
                    value="{{ old('asset_number', $equipment?->asset_number) }}"
                    maxlength="100"
                    placeholder="Company asset number"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >

                @error('asset_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- Machine Details --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Machine Details
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Make
                </label>

                <input
                    type="text"
                    name="make"
                    value="{{ old('make', $equipment?->make) }}"
                    maxlength="150"
                    placeholder="e.g. Tata Hitachi"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Model
                </label>

                <input
                    type="text"
                    name="model"
                    value="{{ old('model', $equipment?->model) }}"
                    maxlength="150"
                    placeholder="e.g. EX 200"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Serial Number
                </label>

                <input
                    type="text"
                    name="serial_number"
                    value="{{ old('serial_number', $equipment?->serial_number) }}"
                    maxlength="150"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Registration Number
                </label>

                <input
                    type="text"
                    name="registration_number"
                    value="{{ old('registration_number', $equipment?->registration_number) }}"
                    maxlength="100"
                    placeholder="Vehicle / statutory registration"
                    class="w-full rounded-lg border-gray-300 uppercase focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Manufacture Year
                </label>

                <input
                    type="number"
                    name="manufacture_year"
                    value="{{ old('manufacture_year', $equipment?->manufacture_year) }}"
                    min="1900"
                    max="{{ now()->year + 1 }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Capacity
                </label>

                <input
                    type="number"
                    name="capacity"
                    value="{{ old('capacity', $equipment?->capacity) }}"
                    step="0.001"
                    min="0"
                    placeholder="e.g. 20"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Capacity Unit
                </label>

                <input
                    type="text"
                    name="capacity_unit"
                    value="{{ old('capacity_unit', $equipment?->capacity_unit) }}"
                    maxlength="50"
                    placeholder="Ton / KVA / L / Cu.m"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

        </div>
    </div>

    {{-- Operational Configuration --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Operational Configuration
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Meter Type
                </label>

                <input
                    type="text"
                    :value="meterType ? meterType.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'None'"
                    readonly
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-700"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Fuel / Power
                </label>

                <input
                    type="text"
                    :value="fuelType ? fuelType.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'None'"
                    readonly
                    class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-700"
                >
            </div>

            <div x-show="meterType !== 'none' && meterType !== ''" x-cloak>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Current Meter Reading
                </label>

                <input
                    type="number"
                    name="current_meter_reading"
                    value="{{ old('current_meter_reading', $equipment?->current_meter_reading) }}"
                    step="0.01"
                    min="0"
                    placeholder="Opening reading"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >

                @error('current_meter_reading')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- Company-Owned Commercial Details --}}
    <div
        x-show="ownership === 'company_owned'"
        x-cloak
        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
    >
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Company Asset Details
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Internal acquisition information for company-owned equipment.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Purchase Date
                </label>

                <input
                    type="date"
                    name="purchase_date"
                    value="{{ old('purchase_date', $equipment?->purchase_date?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Purchase Reference
                </label>

                <input
                    type="text"
                    name="purchase_reference"
                    value="{{ old('purchase_reference', $equipment?->purchase_reference) }}"
                    maxlength="150"
                    placeholder="PO / Invoice / Asset reference"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            {{--
                IMPORTANT:
                Purchase Value intentionally remains an Admin/Commercial
                field. Once role-specific machinery permissions are wired,
                this field should be rendered only for authorized users.
            --}}
            

        </div>
    </div>

    {{-- Current State --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Current State
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Current project is a snapshot. Full allocation and transfer history will be maintained separately.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Current Project
                </label>

                <select
                    name="current_project_id"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Not Allocated</option>

                    @foreach($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected((string) old('current_project_id', $equipment?->current_project_id) === (string) $project->id)
                        >
                            {{ $project->project_name }}
@if($project->project_code)
    ({{ $project->project_code }})
@endif
                        </option>
                    @endforeach
                </select>

                @error('current_project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Status <span class="text-red-500">*</span>
                </label>

                <select
                    name="status"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    @foreach($statuses as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($selectedStatus === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Commissioned Date
                </label>

                <input
                    type="date"
                    name="commissioned_date"
                    value="{{ old('commissioned_date', $equipment?->commissioned_date?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Decommissioned Date
                </label>

                <input
                    type="date"
                    name="decommissioned_date"
                    value="{{ old('decommissioned_date', $equipment?->decommissioned_date?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Remarks
                </label>

                <textarea
                    name="remarks"
                    rows="3"
                    maxlength="5000"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Equipment condition, identification notes or other information..."
                >{{ old('remarks', $equipment?->remarks) }}</textarea>
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $equipment?->is_active ?? true))
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="text-sm font-semibold text-gray-700">
                        Active Equipment
                    </span>
                </label>
            </div>

        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('machinery-equipment.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
        >
            {{ $submitLabel ?? 'Save Equipment' }}
        </button>
    </div>
</div>