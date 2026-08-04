<x-rds.section
    title="Working Status Information"
    description="Define how this working condition should behave in attendance, idle manpower reporting, and PMO review."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.input
            name="code"
            label="Working Status Code"
            value="{{ old('code', $workingStatus->code ?? '') }}"
            placeholder="Example: WAITING_MATERIAL"
            maxlength="40"
            required
        />

        <x-rds.input
            name="name"
            label="Working Status Name"
            value="{{ old('name', $workingStatus->name ?? '') }}"
            placeholder="Example: Waiting for Material"
            maxlength="150"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old('sort_order', $workingStatus->sort_order ?? 0) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $workingStatus->remarks ?? '') }}"
                placeholder="Add notes about when this working status should be used."
            />
        </div>

    </div>
</x-rds.section>

<x-rds.section
    title="Working Status Behaviour"
    description="Configure whether this status contributes to idle manpower and whether a reason is mandatory."
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="counts_as_idle"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'counts_as_idle',
                    $workingStatus->counts_as_idle ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Counts as Idle
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Include labour with this working status in idle manpower summaries and PMO exception reports.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="requires_reason"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'requires_reason',
                    $workingStatus->requires_reason ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Reason Required
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Require remarks or a reason when this working status is selected in attendance.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 md:col-span-2">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'is_active',
                    $workingStatus->is_active ?? true
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Active
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Active working statuses are available in the Labour Attendance interface.
                </span>
            </span>
        </label>

    </div>
</x-rds.section>

@if(isset($workingStatus) && $workingStatus->is_system)
    <x-rds.alert type="warning">
        This is a protected system working status and is read-only.
    </x-rds.alert>
@endif