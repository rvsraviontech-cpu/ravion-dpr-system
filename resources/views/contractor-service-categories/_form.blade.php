<x-rds.section
    title="Basic Information"
    description="Map contractor service categories to the relevant work division."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.select name="activity_division_id" label="Work Division">
            <option value="">Select Work Division</option>

            @foreach($activityDivisions as $division)
                <option value="{{ $division->id }}"
                    @selected(old('activity_division_id', $contractorServiceCategory->activity_division_id ?? '') == $division->id)>
                    {{ $division->code }} - {{ $division->name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.input
            name="code"
            label="Code"
            value="{{ old('code', $contractorServiceCategory->code ?? '') }}"
            placeholder="Example: ELEC"
        />

        <div class="md:col-span-2">
            <x-rds.input
                name="name"
                label="Service Category Name"
                value="{{ old('name', $contractorServiceCategory->name ?? '') }}"
                placeholder="Example: Electrical Contractor"
                required
            />
        </div>

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $contractorServiceCategory->remarks ?? '') }}"
            />
        </div>

        <div class="flex items-center pt-2 md:col-span-2">
            <label class="inline-flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="rounded border-gray-300"
                    @checked(old('is_active', $contractorServiceCategory->is_active ?? true))
                >

                <span class="text-sm font-semibold text-gray-700">
                    Active
                </span>
            </label>
        </div>

    </div>
</x-rds.section>