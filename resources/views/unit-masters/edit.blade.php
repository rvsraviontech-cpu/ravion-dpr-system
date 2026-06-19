@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">
                Edit Unit
            </h1>

            <p class="text-gray-500 mt-1">
                Update unit name, code and status.
            </p>
        </div>

        <a href="{{ route('unit-masters.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">

        <form method="POST"
              action="{{ route('unit-masters.update', $unitMaster) }}">

            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block font-semibold mb-2">
                    Unit Name
                </label>

                <input type="text"
                       name="unit_name"
                       value="{{ old('unit_name', $unitMaster->unit_name) }}"
                       class="border rounded w-full p-3"
                       required>
            </div>

            <div class="mb-5">
                <label class="block font-semibold mb-2">
                    Unit Code
                </label>

                <input type="text"
                       name="unit_code"
                       value="{{ old('unit_code', $unitMaster->unit_code) }}"
                       class="border rounded w-full p-3">
            </div>

            <div class="mb-5">
                <label class="block font-semibold mb-2">
                    Status
                </label>

                <select name="is_active"
                        class="border rounded w-full p-3"
                        required>
                    <option value="1" {{ $unitMaster->is_active ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="0" {{ !$unitMaster->is_active ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    Remarks
                </label>

                <textarea name="remarks"
                          rows="3"
                          class="border rounded w-full p-3">{{ old('remarks', $unitMaster->remarks) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">
                    Update Unit
                </button>

                <a href="{{ route('unit-masters.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

@endsection