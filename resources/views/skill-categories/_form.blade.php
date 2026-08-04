<x-rds.section
    title="Skill Category Information"
    description="Define the skill classification used for Labour Master, attendance, and manpower reporting."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.input
            name="code"
            label="Skill Code"
            value="{{ old('code', $skillCategory->code ?? '') }}"
            placeholder="Example: SKILLED"
            maxlength="30"
            required
        />

        <x-rds.input
            name="name"
            label="Skill Category Name"
            value="{{ old('name', $skillCategory->name ?? '') }}"
            placeholder="Example: Skilled"
            maxlength="150"
            required
        />

        <x-rds.input
            name="sort_order"
            label="Sort Order"
            type="number"
            value="{{ old('sort_order', $skillCategory->sort_order ?? 0) }}"
            min="0"
            step="1"
            required
        />

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $skillCategory->remarks ?? '') }}"
                placeholder="Add notes about this skill classification."
            />
        </div>

    </div>
</x-rds.section>

<x-rds.section
    title="Status"
    description="Control whether this skill category is available for selection."
>
    <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            class="mt-1 rounded border-gray-300"
            @checked(old(
                'is_active',
                $skillCategory->is_active ?? true
            ))
        >

        <span>
            <span class="block text-sm font-semibold text-gray-800">
                Active
            </span>

            <span class="mt-1 block text-xs text-gray-500">
                Active skill categories are available in Labour Master and attendance forms.
            </span>
        </span>
    </label>
</x-rds.section>

@if(isset($skillCategory) && $skillCategory->is_system)
    <x-rds.alert type="warning">
        This is a protected system skill category and is read-only.
    </x-rds.alert>
@endif