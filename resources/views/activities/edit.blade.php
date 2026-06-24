@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Activity</h1>
            <p class="text-sm text-gray-500">
                Update DPR activity details.
            </p>
        </div>

        <a href="{{ route('activities.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md mb-4 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('activities.update', $activity->id) }}"
          method="POST"
          class="bg-white border rounded-lg shadow-sm overflow-hidden">

        @csrf
        @method('PUT')

        <div class="bg-blue-700 text-white px-5 py-4">
            <h2 class="text-lg font-bold">Activity Information</h2>
            <p class="text-sm text-blue-100">
                Engineers see only activity name and unit. Backend mappings remain hidden.
            </p>
        </div>

        <div class="p-5 space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">
                        Activity Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text"
                           name="activity_name"
                           value="{{ old('activity_name', $activity->activity_name) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        Work Stage <span class="text-red-600">*</span>
                    </label>
                    <select name="work_stage"
                            class="w-full border rounded-md px-3 py-2 text-sm"
                            required>
                        <option value="">Select Work Stage</option>
                        @foreach($workStages as $stage)
                            <option value="{{ $stage }}"
                                {{ old('work_stage', $activity->work_stage) == $stage ? 'selected' : '' }}>
                                {{ $stage }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        Unit / UOM <span class="text-red-600">*</span>
                    </label>
                    <select name="unit"
                            class="w-full border rounded-md px-3 py-2 text-sm"
                            required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit }}"
                                {{ old('unit', $activity->unit) == $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="rounded"
                               {{ old('is_active', $activity->is_active) ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-700">
                            Active
                        </span>
                    </label>
                </div>

            </div>

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-3 text-sm">
                Do not include RH cost code in the activity name. Cost-code mapping is backend-only.
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <a href="{{ route('activities.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-md text-sm">
                    Cancel
                </a>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                    Update Activity
                </button>
            </div>

        </div>

    </form>

</div>

@endsection