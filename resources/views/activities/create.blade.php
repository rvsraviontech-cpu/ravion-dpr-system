@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Activity</h1>
            <p class="text-sm text-gray-500">
                Add DPR activity visible to site engineers.
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

    <form action="{{ route('activities.store') }}"
          method="POST"
          class="bg-white border rounded-lg shadow-sm overflow-hidden">

        @csrf

        <div class="bg-blue-700 text-white px-5 py-4">
            <h2 class="text-lg font-bold">Activity Information</h2>
            <p class="text-sm text-blue-100">
                Work stage, activity name and unit used in DPR.
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
                           value="{{ old('activity_name') }}"
                           placeholder="Example: Toilet wall tile laying"
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
                            <option value="{{ $stage }}" {{ old('work_stage') == $stage ? 'selected' : '' }}>
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
                            <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>
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
                               checked>
                        <span class="text-sm font-semibold text-gray-700">
                            Active
                        </span>
                    </label>
                </div>

            </div>

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-3 text-sm">
                Site engineers will see only the activity name and unit. Cost codes and accounting mappings remain hidden.
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <a href="{{ route('activities.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-md text-sm">
                    Cancel
                </a>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                    Save Activity
                </button>
            </div>

        </div>

    </form>

</div>

@endsection