<x-rds.section
    title="Manpower Source Information"
    description="Define how labour is engaged and whether contractor selection is mandatory."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.input
            name="code"
            label="Source Code"
            value="{{ old('code', $manpowerSource->code ?? '') }}"
            placeholder="Example: COMPANY"
            maxlength="30"
            required
        />

        <x-rds.input
            name="name"
            label="Source Name"
            value="{{ old('name', $manpowerSource->name ?? '') }}"
            placeholder="Example: Company Labour"
            maxlength="150"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old('sort_order', $manpowerSource->sort_order ?? 0) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $manpowerSource->remarks ?? '') }}"
                placeholder="Add notes about when this manpower source should be used."
            />
        </div>

    </div>
</x-rds.section>

<x-rds.section
    title="Source Behaviour"
    description="Configure contractor dependency and availability."
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input
                type="checkbox"
                name="requires_contractor"
                value="1"
                class="mt-1 rounded border-gray-300"
                @checked(old(
                    'requires_contractor',
                    $manpowerSource->requires_contractor ?? false
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Contractor Required
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Require contractor or agency selection when this manpower source is selected.
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
                    $manpowerSource->is_active ?? true
                ))
            >

            <span>
                <span class="block text-sm font-semibold text-gray-800">
                    Active
                </span>

                <span class="mt-1 block text-xs text-gray-500">
                    Active manpower sources are available in Labour Master and attendance forms.
                </span>
            </span>
        </label>

    </div>
</x-rds.section>

@if(isset($manpowerSource) && $manpowerSource->is_system)
    <x-rds.alert type="warning">
        This is a protected system manpower source. It cannot be deactivated.
    </x-rds.alert>
@endif