@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">Map Activity</h1>

<div class="bg-white rounded shadow p-6">

    <div class="mb-6">
        <h2 class="text-xl font-semibold mb-4">DPR Work Item Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div><strong>Project:</strong> {{ $dprWorkItem->dpr->project->project_name ?? '-' }}</div>
            <div><strong>Engineer:</strong> {{ $dprWorkItem->dpr->user->name ?? '-' }}</div>
            <div><strong>Activity:</strong> {{ $dprWorkItem->activity->activity_name ?? '-' }}</div>
            <div><strong>Quantity:</strong> {{ $dprWorkItem->quantity_completed }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('mapping-pending-queue.update', $dprWorkItem->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-2 font-medium">Activity Division</label>
            <select id="activity_division_id" class="border rounded w-full p-3">
                <option value="">Select Activity Division</option>
                @foreach($activityDivisions as $division)
                    <option value="{{ $division->id }}">
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-medium">Activity Mapping</label>
            <select name="activity_mapping_id"
                    id="activity_mapping_id"
                    class="border rounded w-full p-3"
                    required>
                <option value="">Select Mapping</option>

                @foreach($activityMappings as $mapping)
                    <option value="{{ $mapping->id }}"
                            data-division="{{ $mapping->activity_division_id }}">
                        {{ $mapping->activity_name }}
                        |
                        {{ $mapping->division_code ?? '-' }}
                        |
                        {{ $mapping->work_stage ?? '-' }}
                        |
                        {{ $mapping->unit ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded">
            Save Mapping
        </button>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionDropdown = document.getElementById('activity_division_id');
    const mappingDropdown = document.getElementById('activity_mapping_id');
    const allMappings = Array.from(mappingDropdown.options);

    function filterMappings() {
        const selectedDivision = divisionDropdown.value;
        mappingDropdown.innerHTML = '<option value="">Select Mapping</option>';

        allMappings.forEach(option => {
            if (option.value === '' || option.dataset.division === selectedDivision) {
                mappingDropdown.appendChild(option.cloneNode(true));
            }
        });
    }

    divisionDropdown.addEventListener('change', filterMappings);
    filterMappings();
});
</script>

@endsection