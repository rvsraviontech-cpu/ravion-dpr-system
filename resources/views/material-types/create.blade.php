@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200';
    $labelClass = 'block mb-2 text-sm font-semibold text-gray-700';
@endphp

<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-3xl font-bold text-gray-800">
                Add Material Type
            </h1>

            <p class="text-gray-500 mt-1">
                Create reusable material types for inventory.
            </p>

        </div>

        <a href="{{ route('material-types.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded-lg">

            Back

        </a>

    </div>

    @if($errors->any())

        <div class="bg-red-100 border border-red-300 rounded-lg p-4 mb-5">

            <ul class="list-disc ml-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form
        method="POST"
        action="{{ route('material-types.store') }}">

        @csrf

        <div class="bg-white rounded-xl shadow p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Material Category --}}

                <div>

                    <label class="{{ $labelClass }}">

                        Material Category

                    </label>

                    <select
                        id="activity_division_id"
                        class="{{ $inputClass }}"
                        required>

                        <option value="">

                            Select Material Category

                        </option>

                        @foreach($activityDivisions as $division)

                            <option
                                value="{{ $division->id }}">

                                {{ $division->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Material --}}

                <div>

                    <label class="{{ $labelClass }}">

                        Material

                    </label>

                    <select
                        id="activity_id"
                        name="activity_id"
                        class="{{ $inputClass }}"
                        required>

                        <option value="">

                            Select Material

                        </option>

                        @foreach($activities as $activity)

                            <option
                                value="{{ $activity->id }}"
                                data-division="{{ $activity->activity_division_id }}">

                                {{ $activity->activity_name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Material Type --}}

                <div>

                    <label class="{{ $labelClass }}">

                        Material Type

                    </label>

                    <input
                        type="text"
                        name="material_type_name"
                        class="{{ $inputClass }}"
                        value="{{ old('material_type_name') }}"
                        placeholder="Example : Cement"
                        required>

                </div>

                {{-- Internal Code --}}

                <div>

                    <label class="{{ $labelClass }}">

                        Internal Code

                    </label>

                    <input
                        type="text"
                        name="material_type_code"
                        class="{{ $inputClass }}"
                        value="{{ old('material_type_code') }}"
                        placeholder="Optional">

                </div>

                {{-- Default Unit --}}

                <div>

                    <label class="{{ $labelClass }}">

                        Default Unit

                    </label>

                    <select
                        name="unit_master_id"
                        class="{{ $inputClass }}">

                        <option value="">

                            Select Unit

                        </option>

                        @foreach($units as $unit)

                            <option
                                value="{{ $unit->id }}">

                                {{ $unit->unit_name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Sequence --}}

                <div>

                    <label class="{{ $labelClass }}">

                        Sequence

                    </label>

                    <input
                        type="number"
                        name="sequence"
                        value="0"
                        class="{{ $inputClass }}">

                </div>

                {{-- Remarks --}}

                <div class="md:col-span-2">

                    <label class="{{ $labelClass }}">

                        Remarks

                    </label>

                    <textarea
                        rows="4"
                        name="remarks"
                        class="{{ $inputClass }}"></textarea>

                </div>

            </div>

        </div>

        <div class="mt-6 flex gap-3">

            <button
                class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-3 font-semibold">

                Save Material Type

            </button>

            <a
                href="{{ route('material-types.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white rounded-lg px-6 py-3">

                Cancel

            </a>

        </div>

    </form>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const division =
        document.getElementById('activity_division_id');

    const activity =
        document.getElementById('activity_id');

    const original =
        Array.from(activity.options);

    function refreshMaterials(){

        const divisionId =
            division.value;

        activity.innerHTML='';

        activity.add(
            new Option(
                'Select Material',
                ''
            )
        );

        original.forEach(function(option){

            if(option.value==='') return;

            if(option.dataset.division===divisionId){

                activity.add(
                    option.cloneNode(true)
                );

            }

        });

    }

    division.addEventListener(
        'change',
        refreshMaterials
    );
    refreshMaterials();

});

</script>

@endsection