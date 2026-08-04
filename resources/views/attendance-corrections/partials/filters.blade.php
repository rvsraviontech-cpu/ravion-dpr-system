<form
    method="GET"
    action="{{ route('attendance-corrections.index') }}"
    class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <x-rds.input
            name="search"
            label="Search"
            placeholder="Correction no., attendance no., project, or reason"
            :value="request('search')"
        />

        <x-rds.select
            name="project_id"
            label="Project"
        >
            <option value="">All Projects</option>

            @foreach($projects as $project)
                <option
                    value="{{ $project->id }}"
                    @selected(
                        (string) request('project_id')
                        === (string) $project->id
                    )
                >
                    {{ $project->project_name }}

                    @if($project->project_code)
                        — {{ $project->project_code }}
                    @endif
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="status"
            label="Workflow Status"
        >
            <option value="">All Statuses</option>

            @foreach($statuses as $statusValue => $statusLabel)
                <option
                    value="{{ $statusValue }}"
                    @selected(
                        request('status') === $statusValue
                    )
                >
                    {{ $statusLabel }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.input
            name="attendance_date"
            label="Exact Attendance Date"
            type="date"
            :value="request('attendance_date')"
        />

        <x-rds.input
            name="date_from"
            label="Attendance Date From"
            type="date"
            :value="request('date_from')"
        />

        <x-rds.input
            name="date_to"
            label="Attendance Date To"
            type="date"
            :value="request('date_to')"
        />

    </div>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">

        <x-rds.button
            href="{{ route('attendance-corrections.index') }}"
            variant="secondary"
            class="!px-4 !py-2 !text-sm"
        >
            Reset
        </x-rds.button>

        <x-rds.button
            type="submit"
            variant="primary"
            class="!px-4 !py-2 !text-sm"
        >
            Filter
        </x-rds.button>

    </div>
</form>