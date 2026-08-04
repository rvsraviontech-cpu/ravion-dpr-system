<x-rds.section
    title="Gender Information"
    description="Define the gender classification used in Labour Master and attendance records."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.input
            name="code"
            label="Gender Code"
            value="{{ old('code', $gender->code ?? '') }}"
            placeholder="Example: M"
            maxlength="20"
            required
        />

        <x-rds.input
            name="name"
            label="Gender Name"
            value="{{ old('name', $gender->name ?? '') }}"
            placeholder="Example: Male"
            maxlength="100"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old('sort_order', $gender->sort_order ?? 0) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $gender->remarks ?? '') }}"
                placeholder="Add any notes about this gender classification."
            />
        </div>

    </div>
</x-rds.section>

<x-rds.section
    title="Status"
    description="Control whether this gender is available for selection."
>
    <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            class="mt-1 rounded border-gray-300"
            @checked(old(
                'is_active',
                $gender->is_active ?? true
            ))
        >

        <span>
            <span class="block text-sm font-semibold text-gray-800">
                Active
            </span>

            <span class="mt-1 block text-xs text-gray-500">
                Active genders are available in Labour Master and attendance forms.
            </span>
        </span>
    </label>
</x-rds.section>

@if(isset($gender) && $gender->is_system)
    <x-rds.alert type="warning">
        This is a protected system gender record. It cannot be deactivated.
    </x-rds.alert>
@endif