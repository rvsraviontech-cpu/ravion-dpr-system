@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $oldItems = old('items', [
        [
            'activity_division_id' => '',
            'activity_id' => '',
            'material_type_id' => '',
            'brand_master_id' => '',
            'material_specification_id' => '',
            'material_grade_id' => '',
            'quantity_received' => '',
            'unit_master_id' => '',
            'remarks' => '',
        ],
    ]);

    $oldPhotos = old('photos', [
        [
            'photo_type' => 'Material Photo',
            'caption' => '',
            'item_index' => '',
        ],
    ]);

    $activityOptionsForJs = $activities
        ->map(fn ($activity) => [
            'id' => $activity->id,
            'name' => $activity->activity_name,
            'division_id' => $activity->activity_division_id,
        ])
        ->values();

    $materialTypeOptionsForJs = $materialTypes
        ->map(fn ($type) => [
            'id' => $type->id,
            'name' => $type->material_type_name,
            'group' => $type->material_group,
            'unit_id' => $type->unit_master_id,
            'unit_name' => optional($type->unit)->unit_name,
        ])
        ->values();

    $brandOptionsForJs = $brands
        ->map(fn ($brand) => [
            'id' => $brand->id,
            'name' => $brand->brand_name,
            'material_type_id' => $brand->material_type_id,
        ])
        ->values();

    $specificationOptionsForJs = $specifications
        ->map(fn ($specification) => [
            'id' => $specification->id,
            'name' => $specification->specification_name,
            'material_type_id' => $specification->material_type_id,
        ])
        ->values();

    $gradeOptionsForJs = $grades
        ->map(fn ($grade) => [
            'id' => $grade->id,
            'name' => $grade->grade_name,
            'material_type_id' => $grade->material_type_id,
        ])
        ->values();

    $materialGroupsForJs = $materialGroups->values();
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                Add Material Receipt
            </h1>

            <p class="mt-1 text-gray-500">
                Record one delivery containing one or more material items and supporting photos.
            </p>
        </div>

        <a href="{{ route('material-received.index') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-600 px-5 py-3 font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">
            Back
        </a>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
            <p class="mb-2 font-semibold">
                Please correct the following:
            </p>

            <ul class="ml-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('material-received.store') }}"
          id="material-receipt-form"
          enctype="multipart/form-data">

        @csrf

        @include('material-received.partials.delivery-information')
        @include('material-received.partials.material-items')
        @include('material-received.partials.material-photos')

        <div class="sticky bottom-[68px] z-30 mt-6 grid grid-cols-1 gap-3 border-t border-gray-200 bg-white/95 py-3 backdrop-blur sm:flex sm:flex-wrap lg:static lg:border-0 lg:bg-transparent lg:py-0">
            <button type="submit"
                    class="w-full rounded-xl bg-blue-600 px-7 py-3.5 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-3">
                Save Material Receipt
            </button>

            <a href="{{ route('material-received.index') }}"
               class="w-full rounded-xl bg-gray-500 px-7 py-3.5 text-center font-semibold text-white hover:bg-gray-600 sm:w-auto sm:py-3">
                Cancel
            </a>
        </div>

    </form>
</div>

@include('material-received.partials.javascript')

@endsection
