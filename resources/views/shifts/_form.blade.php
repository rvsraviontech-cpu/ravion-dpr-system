<x-rds.section
    title="Shift Information"
    description="Define shift timings, normal working hours, overtime start, grace periods, overnight behaviour, and availability."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.input
            name="code"
            label="Shift Code"
            value="{{ old('code', $shift->code ?? '') }}"
            placeholder="Example: DAY"
            maxlength="30"
            required
            :disabled="isset($shift) && $shift->is_system"
        />

        @if(isset($shift) && $shift->is_system)
            <input
                type="hidden"
                name="code"
                value="{{ $shift->code }}"
            >
        @endif

        <x-rds.input
            name="name"
            label="Shift Name"
            value="{{ old('name', $shift->name ?? '') }}"
            placeholder="Example: Day Shift"
            maxlength="150"
            required
        />

        <x-rds.input
            name="start_time"
            label="Start Time"
            type="time"
            value="{{ old(
                'start_time',
                isset($shift) && $shift->start_time
                    ? substr((string) $shift->start_time, 0, 5)
                    : ''
            ) }}"
            required
        />

        <x-rds.input
            name="end_time"
            label="End Time"
            type="time"
            value="{{ old(
                'end_time',
                isset($shift) && $shift->end_time
                    ? substr((string) $shift->end_time, 0, 5)
                    : ''
            ) }}"
            required
        />

        <x-rds.input
            name="normal_hours"
            label="Normal Working Hours"
            type="number"
            value="{{ old(
                'normal_hours',
                $shift->normal_hours ?? '8.00'
            ) }}"
            min="0.25"
            max="24"
            step="0.25"
            required
        />

        <x-rds.input
            name="ot_start_time"
            label="OT Start Time"
            type="time"
            value="{{ old(
                'ot_start_time',
                isset($shift) && $shift->ot_start_time
                    ? substr((string) $shift->ot_start_time, 0, 5)
                    : (
                        isset($shift) && $shift->end_time
                            ? substr((string) $shift->end_time, 0, 5)
                            : ''
                    )
            ) }}"
        />

        <x-rds.input
            name="grace_in_minutes"
            label="Check-In Grace (Minutes)"
            type="number"
            value="{{ old(
                'grace_in_minutes',
                $shift->grace_in_minutes ?? 0
            ) }}"
            min="0"
            max="240"
            step="1"
            required
        />

        <x-rds.input
            name="grace_out_minutes"
            label="Check-Out Grace (Minutes)"
            type="number"
            value="{{ old(
                'grace_out_minutes',
                $shift->grace_out_minutes ?? 0
            ) }}"
            min="0"
            max="240"
            step="1"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old(
                'sort_order',
                $shift->sort_order ?? 0
            ) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old(
                    'remarks',
                    $shift->remarks ?? ''
                ) }}"
                placeholder="Add notes about when this shift should be used."
            />
        </div>

    </div>
</x-rds.section>

<x-rds.section
    title="Shift Behaviour"
    description="Configure overnight handling and availability."
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="crosses_midnight"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'crosses_midnight',
                    $shift->crosses_midnight ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Crosses Midnight
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Enable this when the shift ends on the next calendar day, such as 8:00 PM to 5:00 AM.
                </span>
            </span>
        </label>

        @if(isset($shift) && $shift->is_system)

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <div class="text-sm font-semibold text-amber-800">
                    Protected System Shift
                </div>

                <div class="mt-1 text-xs leading-5 text-amber-700">
                    This shift can be edited, but its code, system-record status, and active status remain protected.
                </div>

                <input
                    type="hidden"
                    name="is_active"
                    value="1"
                >
            </div>

        @else

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="mt-1 rounded border-gray-300"
                    @checked(old(
                        'is_active',
                        $shift->is_active ?? true
                    ))
                >

                <span>
                    <span class="block text-sm font-semibold text-gray-800">
                        Active
                    </span>

                    <span class="mt-1 block text-xs text-gray-500">
                        Active shifts are available in Labour Master and Labour Attendance forms.
                    </span>
                </span>
            </label>

        @endif

    </div>
</x-rds.section>

@if(isset($shift) && $shift->is_system)
    <x-rds.alert type="warning">
        This system shift is editable, but it cannot be deactivated and its shift code remains protected.
    </x-rds.alert>
@endif
