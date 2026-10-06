@php
    $machineryTool = $machineryTool ?? null;
@endphp

<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Code --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Equipment Code <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="code"
                value="{{ old('code', $machineryTool?->code) }}"
                maxlength="50"
                required
                placeholder="e.g. EXCAVATOR"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >

            <p class="mt-1 text-xs text-gray-500">
                Unique master code. Example: EXCAVATOR, DG_SET, NEEDLE_VIBRATOR.
            </p>

            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Machine Name --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Equipment Name <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="machine_name"
                value="{{ old('machine_name', $machineryTool?->machine_name) }}"
                required
                placeholder="e.g. Hydraulic Excavator"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >

            @error('machine_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category --}}
        {{-- Category --}}
<div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">
        Category <span class="text-red-500">*</span>
    </label>

    <select
        name="category"
        required
        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
    >
        <option value="">Select Category</option>

        @foreach($categories as $category)
            <option
                value="{{ $category }}"
                @selected(old('category', $machineryTool?->category) === $category)
            >
                {{ $category }}
            </option>
        @endforeach
    </select>

    @error('category')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

        {{-- Unit --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Default Unit <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="unit"
                value="{{ old('unit', $machineryTool?->unit ?? 'Nos') }}"
                required
                placeholder="Nos"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >

            @error('unit')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tracking Mode --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Tracking Mode <span class="text-red-500">*</span>
            </label>

            <select
                name="tracking_mode"
                required
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="individual"
                    @selected(old('tracking_mode', $machineryTool?->tracking_mode ?? 'individual') === 'individual')>
                    Individual / Serialized
                </option>

                <option value="pooled"
                    @selected(old('tracking_mode', $machineryTool?->tracking_mode) === 'pooled')>
                    Pooled / Quantity Based
                </option>
            </select>

            <p class="mt-1 text-xs text-gray-500">
                Use Individual for identifiable machines and Pooled for quantity-based tools.
            </p>

            @error('tracking_mode')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Meter Type --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Meter Type <span class="text-red-500">*</span>
            </label>

            <select
                name="meter_type"
                required
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="none"
                    @selected(old('meter_type', $machineryTool?->meter_type ?? 'none') === 'none')>
                    No Meter
                </option>

                <option value="hour_meter"
                    @selected(old('meter_type', $machineryTool?->meter_type) === 'hour_meter')>
                    Hour Meter
                </option>

                <option value="odometer"
                    @selected(old('meter_type', $machineryTool?->meter_type) === 'odometer')>
                    Odometer
                </option>
            </select>

            @error('meter_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fuel / Power --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Fuel / Power Type <span class="text-red-500">*</span>
            </label>

            <select
                name="fuel_type"
                required
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
                @foreach([
                    'none' => 'None',
                    'diesel' => 'Diesel',
                    'petrol' => 'Petrol',
                    'electric' => 'Electric',
                    'battery' => 'Battery',
                    'hybrid' => 'Hybrid',
                    'other' => 'Other',
                ] as $value => $label)

                    <option
                        value="{{ $value }}"
                        @selected(old('fuel_type', $machineryTool?->fuel_type ?? 'none') === $value)
                    >
                        {{ $label }}
                    </option>

                @endforeach
            </select>

            @error('fuel_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Sort Order --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Sort Order
            </label>

            <input
                type="number"
                name="sort_order"
                min="0"
                value="{{ old('sort_order', $machineryTool?->sort_order ?? 0) }}"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >

            @error('sort_order')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Behaviour --}}
    <div class="border border-gray-200 rounded-xl p-5">
        <h3 class="font-bold text-gray-900 mb-4">
            Operational Behaviour
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    name="requires_operator"
                    value="1"
                    @checked(old('requires_operator', $machineryTool?->requires_operator ?? false))
                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >

                <span>
                    <span class="block text-sm font-semibold text-gray-800">
                        Operator Required
                    </span>
                    <span class="block text-xs text-gray-500">
                        Daily usage should capture the operator.
                    </span>
                </span>
            </label>

            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    name="requires_meter_reading"
                    value="1"
                    @checked(old('requires_meter_reading', $machineryTool?->requires_meter_reading ?? false))
                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >

                <span>
                    <span class="block text-sm font-semibold text-gray-800">
                        Meter Reading Required
                    </span>
                    <span class="block text-xs text-gray-500">
                        Capture opening and closing meter readings.
                    </span>
                </span>
            </label>

            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    name="requires_fuel_tracking"
                    value="1"
                    @checked(old('requires_fuel_tracking', $machineryTool?->requires_fuel_tracking ?? false))
                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >

                <span>
                    <span class="block text-sm font-semibold text-gray-800">
                        Fuel Tracking Required
                    </span>
                    <span class="block text-xs text-gray-500">
                        Daily logs may capture fuel issued/consumed.
                    </span>
                </span>
            </label>

            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $machineryTool?->is_active ?? true))
                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >

                <span>
                    <span class="block text-sm font-semibold text-gray-800">
                        Active
                    </span>
                    <span class="block text-xs text-gray-500">
                        Available for future equipment registration and reporting.
                    </span>
                </span>
            </label>

        </div>
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
            Description
        </label>

        <textarea
            name="description"
            rows="4"
            placeholder="Optional description or usage notes..."
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
        >{{ old('description', $machineryTool?->description) }}</textarea>

        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>