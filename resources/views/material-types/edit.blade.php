@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';
@endphp

<div class="mx-auto max-w-5xl">

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Material Type
            </h1>

            <p class="mt-1 text-gray-500">
                Update the material group, default unit and other master details.
            </p>
        </div>

        <a href="{{ route('material-types.index') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
            Back
        </a>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">

            <ul class="ml-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    <form method="POST"
          action="{{ route('material-types.update', $materialType) }}">

        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Material Type Details
            </h2>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label class="{{ $labelClass }}">
                        Material Group <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="material_group"
                           value="{{ old('material_group', $materialType->material_group) }}"
                           list="material-group-options"
                           class="{{ $inputClass }}"
                           placeholder="Example: Electrical"
                           required>

                    <datalist id="material-group-options">
                        @foreach($materialGroups as $group)
                            <option value="{{ $group }}">
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Material Type <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="material_type_name"
                           value="{{ old('material_type_name', $materialType->material_type_name) }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: MCB"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Default Unit <span class="text-red-500">*</span>
                    </label>

                    <select name="unit_master_id"
                            class="{{ $inputClass }}"
                            required>

                        <option value="">
                            Select Default Unit
                        </option>

                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}"
                                {{ (string) old('unit_master_id', $materialType->unit_master_id) === (string) $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Display Sequence
                    </label>

                    <input type="number"
                           name="sequence"
                           value="{{ old('sequence', $materialType->sequence) }}"
                           min="0"
                           class="{{ $inputClass }}">
                </div>

                <div class="flex items-center">

                    <label class="inline-flex items-center gap-3">

                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $materialType->is_active) ? 'checked' : '' }}>

                        <span class="text-sm font-semibold text-gray-700">
                            Active
                        </span>

                    </label>

                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">
                        Remarks
                    </label>

                    <textarea name="remarks"
                              rows="4"
                              class="{{ $inputClass }}"
                              placeholder="Optional notes">{{ old('remarks', $materialType->remarks) }}</textarea>
                </div>

            </div>

        </div>

        <div class="mt-6 flex flex-wrap gap-3">

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                Update Material Type
            </button>

            <a href="{{ route('material-types.index') }}"
               class="rounded-lg bg-gray-500 px-6 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection