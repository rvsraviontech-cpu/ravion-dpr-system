<x-rds.section
    title="Basic Information"
    description="Engineers see only activity name, unit and work stage. Backend mappings remain hidden."
>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

        <x-rds.select
            name="activity_division_id"
            label="Activity Division"
            required
        >
            <option value="">Select Activity Division</option>

            @foreach($activityDivisions as $division)
                <option value="{{ $division->id }}"
                    @selected(old('activity_division_id', $activity->activity_division_id ?? '') == $division->id)>
                    {{ $division->name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="work_stage"
            label="Work Stage"
            required
        >
            <option value="">Select Work Stage</option>

            @foreach($workStages as $stage)
                <option value="{{ $stage->name }}"
                    @selected(old('work_stage', $activity->work_stage ?? '') == $stage->name)>
                    {{ $stage->name }}
                </option>
            @endforeach
        </x-rds.select>

        <div class="md:col-span-2">
            <x-rds.input
                name="activity_name"
                label="Activity Name"
                value="{{ old('activity_name', $activity->activity_name ?? '') }}"
                placeholder="Example: Toilet wall tile laying"
                required
            />
        </div>

        <x-rds.select
            name="unit"
            label="Unit / UOM"
            required
        >
            <option value="">Select Unit</option>

            @foreach($units as $unit)
                <option value="{{ $unit->unit_name }}"
                    @selected(old('unit', $activity->unit ?? '') == $unit->unit_name)>
                    {{ $unit->unit_name }} @if(!empty($unit->symbol)) ({{ $unit->symbol }}) @endif
                </option>
            @endforeach
        </x-rds.select>

        <div class="flex items-center pt-6">
            <label class="inline-flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="rounded border-gray-300"
                    @checked(old('is_active', $activity->is_active ?? true))
                >

                <span class="text-sm font-semibold text-gray-700">
                    Active
                </span>
            </label>
        </div>

        <div class="md:col-span-2">
            <x-rds.textarea
                name="remarks"
                label="Remarks"
                rows="3"
                value="{{ old('remarks', $activity->remarks ?? '') }}"
            />
        </div>

    </div>
</x-rds.section>