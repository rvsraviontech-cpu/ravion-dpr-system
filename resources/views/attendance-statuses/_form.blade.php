<x-rds.section
    title="Attendance Status Information"
    description="Define how this attendance status behaves in attendance summaries, working hours, and payable calculations."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.input
            name="code"
            label="Status Code"
            value="{{ old('code', $attendanceStatus->code ?? '') }}"
            placeholder="Example: P"
            maxlength="20"
            required
        />

        <x-rds.input
            name="name"
            label="Status Name"
            value="{{ old('name', $attendanceStatus->name ?? '') }}"
            placeholder="Example: Present"
            maxlength="100"
            required
        />

        <x-rds.input
            name="short_name"
            label="Short Name"
            value="{{ old('short_name', $attendanceStatus->short_name ?? '') }}"
            placeholder="Example: Present"
            maxlength="50"
        />

        <x-rds.input
            name="payable_factor"
            label="Payable Factor"
            type="number"
            value="{{ old('payable_factor', $attendanceStatus->payable_factor ?? '0.00') }}"
            min="0"
            max="1"
            step="0.01"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old('sort_order', $attendanceStatus->sort_order ?? 0) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $attendanceStatus->remarks ?? '') }}"
                placeholder="Add a short description of when this attendance status should be used."
            />
        </div>

    </div>
</x-rds.section>

<x-rds.section
    title="Attendance Behaviour"
    description="Configure how this status contributes to manpower reporting and attendance validation."
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="counts_as_present"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'counts_as_present',
                    $attendanceStatus->counts_as_present ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Counts as Present
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Include labour with this status in present manpower totals and DPR present-labour tables.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="counts_as_absent"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'counts_as_absent',
                    $attendanceStatus->counts_as_absent ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Counts as Absent
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Include labour with this status in absent manpower totals.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="allows_normal_hours"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'allows_normal_hours',
                    $attendanceStatus->allows_normal_hours ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Allow Normal Hours
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Permit the attendance interface to record normal working hours.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="allows_ot_hours"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'allows_ot_hours',
                    $attendanceStatus->allows_ot_hours ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Allow OT Hours
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Permit overtime hours for labour marked with this status.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="requires_working_status"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'requires_working_status',
                    $attendanceStatus->requires_working_status ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Require Working Status
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Require Working, Idle, Waiting for Material, or another working status during attendance entry.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'is_active',
                    $attendanceStatus->is_active ?? true
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Active
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Active statuses are available for selection in daily attendance.
                </span>
            </span>
        </label>

    </div>
</x-rds.section>

@if(isset($attendanceStatus) && $attendanceStatus->is_system)
    <x-rds.alert type="warning">
        This is a protected system attendance status. Its system flag cannot be changed, and it cannot be deactivated.
    </x-rds.alert>
@endif