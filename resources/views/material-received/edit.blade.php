@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-base text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:py-2.5 sm:text-sm';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $existingItems = $materialReceived->items->map(function ($item) {
        $pending = $item->pendingClassification;
        return [
            'entry_mode' => $pending ? 'temporary' : 'existing',
            'material_type_id' => $item->material_type_id,
            'brand_master_id' => $item->brand_master_id,
            'material_specification_id' => $item->material_specification_id,
            'material_grade_id' => $item->material_grade_id,
            'temporary_material_name' => $pending?->raw_material_name,
            'temporary_brand' => $pending?->raw_brand,
            'temporary_specification' => $pending?->raw_specification,
            'temporary_grade' => $pending?->raw_grade,
            'temporary_classification_notes' => $pending?->remarks,
            'quantity_received' => $item->quantity_received,
            'unit_master_id' => $item->unit_master_id,
            'purpose_used_for' => $item->purpose_used_for,
            'remarks' => $item->remarks,
        ];
    })->values()->all();

    if (empty($existingItems)) {
        $existingItems = [[
            'entry_mode' => 'existing', 'material_type_id' => '', 'brand_master_id' => '',
            'material_specification_id' => '', 'material_grade_id' => '',
            'temporary_material_name' => '', 'temporary_brand' => '',
            'temporary_specification' => '', 'temporary_grade' => '',
            'temporary_classification_notes' => '', 'quantity_received' => '',
            'unit_master_id' => '', 'purpose_used_for' => '', 'remarks' => '',
        ]];
    }

    $oldItems = old('items', $existingItems);

    $oldPhotos = old('photos', [[
        'photo_type' => 'Material Photo',
        'caption' => '',
        'item_index' => '',
    ]]);

    $materialTypeOptionsForJs = $materialTypes->map(fn ($type) => [
        'id' => $type->id,
        'name' => $type->material_type_name,
        'group' => $type->material_group,
        'unit_id' => $type->unit_master_id,
        'unit_name' => optional($type->unit)->unit_name,
        'search' => strtolower(collect([
            $type->material_type_name,
            $type->material_group,
            $brands->where('material_type_id', $type->id)->pluck('brand_name')->implode(' '),
            $specifications->where('material_type_id', $type->id)->pluck('specification_name')->implode(' '),
            $grades->where('material_type_id', $type->id)->pluck('grade_name')->implode(' '),
        ])->filter()->implode(' ')),
    ])->values();

    $brandOptionsForJs = $brands->map(fn ($brand) => [
        'id' => $brand->id, 'name' => $brand->brand_name, 'material_type_id' => $brand->material_type_id,
    ])->values();

    $specificationOptionsForJs = $specifications->map(fn ($specification) => [
        'id' => $specification->id, 'name' => $specification->specification_name, 'material_type_id' => $specification->material_type_id,
    ])->values();

    $gradeOptionsForJs = $grades->map(fn ($grade) => [
        'id' => $grade->id, 'name' => $grade->grade_name, 'material_type_id' => $grade->material_type_id,
    ])->values();

    $unitOptionsForJs = $units->map(fn ($unit) => [
        'id' => $unit->id, 'name' => $unit->unit_name,
    ])->values();
@endphp

<div class="mx-auto max-w-full">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Edit Material Receipt #{{ $materialReceived->id }}</h1>
            <p class="mt-1 text-gray-500">Edit what physically arrived at site. No activity or work-package classification is required.</p>
        </div>
        <a href="{{ route('material-received.index') }}"
           class="inline-flex w-full items-center justify-center rounded-lg bg-gray-600 px-5 py-3 font-semibold text-white hover:bg-gray-700 sm:w-auto sm:py-2.5">Back</a>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            <ul class="ml-5 list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('material-received.update', $materialReceived) }}" id="material-receipt-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('material-received.partials.delivery-information')
        @include('material-received.partials.material-items')
        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 p-5">
                <h2 class="text-xl font-bold text-gray-800">Existing Photos</h2>
                <p class="mt-1 text-sm text-gray-500">Tick photos that should be removed when this Draft is updated.</p>
            </div>
            @if($materialReceived->photos->isNotEmpty())
                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($materialReceived->photos as $photo)
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <a href="{{ $photo->file_url }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $photo->file_url }}" alt="{{ $photo->display_caption }}" class="h-44 w-full object-cover">
                            </a>
                            <div class="p-3">
                                <div class="text-sm font-semibold text-gray-700">{{ $photo->photo_type }}</div>
                                <label class="mt-3 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2">
                                    <input type="checkbox" name="remove_photo_ids[]" value="{{ $photo->id }}"
                                           {{ in_array((string) $photo->id, array_map('strval', old('remove_photo_ids', [])), true) ? 'checked' : '' }}>
                                    <span class="text-sm font-semibold text-red-700">Remove on Update</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-sm text-gray-500">No existing photos.</div>
            @endif
        </div>

        @include('material-received.partials.material-photos')

        <div class="sticky bottom-[68px] z-30 mt-6 grid grid-cols-1 gap-3 border-t border-gray-200 bg-white/95 py-3 backdrop-blur sm:flex sm:flex-wrap lg:static lg:border-0 lg:bg-transparent lg:py-0">
            <button type="submit" class="w-full rounded-xl bg-blue-600 px-7 py-3.5 font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto sm:py-3">Update Material Receipt</button>
            <a href="{{ route('material-received.show', $materialReceived) }}" class="w-full rounded-xl bg-gray-500 px-7 py-3.5 text-center font-semibold text-white hover:bg-gray-600 sm:w-auto sm:py-3">Cancel</a>
        </div>
    </form>
</div>

@include('material-received.partials.javascript')
@endsection
