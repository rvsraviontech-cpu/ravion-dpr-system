@extends('layouts.app')

@section('content')

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1 block text-sm font-semibold text-gray-700';

    $existingItems = $materialReceived->items->map(function ($item) {
        return [
            'activity_division_id' => $item->activity_division_id,
            'activity_id' => $item->activity_id,
            'material_type_id' => $item->material_type_id,
            'brand_master_id' => $item->brand_master_id,
            'material_specification_id' => $item->material_specification_id,
            'material_grade_id' => $item->material_grade_id,
            'quantity_received' => $item->quantity_received,
            'unit_master_id' => $item->unit_master_id,
            'remarks' => $item->remarks,
        ];
    })->values()->all();

    if (empty($existingItems)) {
        $existingItems = [[
            'activity_division_id' => '',
            'activity_id' => '',
            'material_type_id' => '',
            'brand_master_id' => '',
            'material_specification_id' => '',
            'material_grade_id' => '',
            'quantity_received' => '',
            'unit_master_id' => '',
            'remarks' => '',
        ]];
    }

    $formItems = old('items', $existingItems);

    $activityOptionsForJs = $activities
        ->map(function ($activity) {
            return [
                'id' => $activity->id,
                'name' => $activity->activity_name,
                'division_id' => $activity->activity_division_id,
            ];
        })
        ->values();

    $materialTypeOptionsForJs = $materialTypes
        ->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->material_type_name,
                'group' => $type->material_group,
                'unit_id' => $type->unit_master_id,
                'unit_name' => optional($type->unit)->unit_name,
            ];
        })
        ->values();

    $brandOptionsForJs = $brands
        ->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->brand_name,
                'material_type_id' => $brand->material_type_id,
            ];
        })
        ->values();

    $specificationOptionsForJs = $specifications
        ->map(function ($specification) {
            return [
                'id' => $specification->id,
                'name' => $specification->specification_name,
                'material_type_id' => $specification->material_type_id,
            ];
        })
        ->values();

    $gradeOptionsForJs = $grades
        ->map(function ($grade) {
            return [
                'id' => $grade->id,
                'name' => $grade->grade_name,
                'material_type_id' => $grade->material_type_id,
            ];
        })
        ->values();

    $materialGroupsForJs = $materialGroups->values();

    $oldPhotos = old('photos', [
        [
            'photo_type' => 'Material Photo',
            'caption' => '',
            'item_index' => '',
        ],
    ]);
@endphp

<div class="mx-auto max-w-full">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Material Receipt #{{ $materialReceived->id }}
            </h1>

            <p class="mt-1 text-gray-500">
                Update delivery information and material item rows while this receipt is in Draft status.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('material-received.show', $materialReceived) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-700 px-5 py-2.5 font-semibold text-white hover:bg-slate-800">
                View
            </a>

            <a href="{{ route('material-received.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-5 py-2.5 font-semibold text-white hover:bg-gray-700">
                Back
            </a>
        </div>
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
          action="{{ route('material-received.update', $materialReceived) }}"
          id="material-receipt-form"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- Delivery Information --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="mb-5 text-xl font-bold text-gray-800">
                Delivery Information
            </h2>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

                <div>
                    <label class="{{ $labelClass }}">
                        Project <span class="text-red-500">*</span>
                    </label>

                    <select name="project_id"
                            id="project_id"
                            class="{{ $inputClass }}"
                            required>

                        <option value="">Select Project</option>

                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                {{ (string) old('project_id', $materialReceived->project_id) === (string) $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Block</label>

                    <select name="project_block_id"
                            id="project_block_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Block</option>

                        @foreach($projectBlocks as $block)
                            <option value="{{ $block->id }}"
                                    data-project="{{ $block->project_id }}"
                                {{ (string) old('project_block_id', $materialReceived->project_block_id) === (string) $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Floor</label>

                    <select name="project_floor_id"
                            id="project_floor_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Floor</option>

                        @foreach($projectFloors as $floor)
                            <option value="{{ $floor->id }}"
                                    data-project="{{ $floor->project_id }}"
                                    data-block="{{ $floor->project_block_id }}"
                                {{ (string) old('project_floor_id', $materialReceived->project_floor_id) === (string) $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Unit</label>

                    <select name="project_unit_id"
                            id="project_unit_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Unit</option>

                        @foreach($projectUnits as $projectUnit)
                            <option value="{{ $projectUnit->id }}"
                                    data-project="{{ $projectUnit->project_id }}"
                                    data-block="{{ $projectUnit->project_block_id }}"
                                    data-floor="{{ $projectUnit->project_floor_id }}"
                                {{ (string) old('project_unit_id', $materialReceived->project_unit_id) === (string) $projectUnit->id ? 'selected' : '' }}>
                                {{ $projectUnit->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Storage Location</label>

                    <input type="text"
                           name="storage_location"
                           value="{{ old('storage_location', $materialReceived->storage_location) }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: Site Store, Ground Floor Yard">
                </div>

                <div>
                    <label class="{{ $labelClass }}">
                        Received Date <span class="text-red-500">*</span>
                    </label>

                    <input type="date"
                           name="received_date"
                           value="{{ old('received_date', $materialReceived->received_date?->format('Y-m-d')) }}"
                           class="{{ $inputClass }}"
                           required>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Vendor</label>

                    <select name="vendor_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Vendor</option>

                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}"
                                {{ (string) old('vendor_id', $materialReceived->vendor_id) === (string) $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->vendor_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Contractor Supply</label>

                    <label class="mt-3 inline-flex items-center gap-3">
                        <input type="checkbox"
                               name="supplied_by_contractor"
                               id="supplied_by_contractor"
                               value="1"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('supplied_by_contractor', $materialReceived->supplied_by_contractor) ? 'checked' : '' }}>

                        <span class="text-sm text-gray-700">
                            Supplied by contractor
                        </span>
                    </label>
                </div>

                <div id="contractor-wrapper">
                    <label class="{{ $labelClass }}">Contractor</label>

                    <select name="contractor_id"
                            id="contractor_id"
                            class="{{ $inputClass }}">

                        <option value="">Select Contractor</option>

                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}"
                                {{ (string) old('contractor_id', $materialReceived->contractor_id) === (string) $contractor->id ? 'selected' : '' }}>
                                {{ $contractor->contractor_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Vehicle Number</label>

                    <input type="text"
                           name="vehicle_number"
                           value="{{ old('vehicle_number', $materialReceived->vehicle_number) }}"
                           class="{{ $inputClass }}"
                           placeholder="Example: TS09AB1234">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Driver Name</label>

                    <input type="text"
                           name="driver_name"
                           value="{{ old('driver_name', $materialReceived->driver_name) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Challan Number</label>

                    <input type="text"
                           name="challan_number"
                           value="{{ old('challan_number', $materialReceived->challan_number) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Bill Number</label>

                    <input type="text"
                           name="bill_number"
                           value="{{ old('bill_number', $materialReceived->bill_number) }}"
                           class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="{{ $labelClass }}">Delivery Remarks</label>

                    <textarea name="remarks"
                              rows="3"
                              class="{{ $inputClass }}"
                              placeholder="General notes for this delivery">{{ old('remarks', $materialReceived->remarks) }}</textarea>
                </div>

            </div>
        </div>

        {{-- Material Items --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Material Items
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Edit existing items or add more materials to this delivery.
                    </p>
                </div>

                <button type="button"
                        id="add-item-row"
                        class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">
                    + Add Material Row
                </button>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-[1800px] w-full text-sm">

                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="w-14 px-3 py-3 text-center">#</th>
                            <th class="min-w-44 px-3 py-3 text-left">Activity Division</th>
                            <th class="min-w-52 px-3 py-3 text-left">Activity / Material</th>
                            <th class="min-w-48 px-3 py-3 text-left">Material Group</th>
                            <th class="min-w-52 px-3 py-3 text-left">Material Type</th>
                            <th class="min-w-44 px-3 py-3 text-left">Brand</th>
                            <th class="min-w-44 px-3 py-3 text-left">Specification</th>
                            <th class="min-w-44 px-3 py-3 text-left">Grade / Rating</th>
                            <th class="min-w-36 px-3 py-3 text-left">Quantity</th>
                            <th class="min-w-36 px-3 py-3 text-left">Unit</th>
                            <th class="min-w-52 px-3 py-3 text-left">Remarks</th>
                            <th class="w-24 px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody id="material-items-body"
                           class="divide-y divide-gray-200">

                        @foreach($formItems as $rowIndex => $formItem)
                            <tr class="material-item-row align-top"
                                data-row-index="{{ $rowIndex }}">

                                <td class="row-number px-3 py-3 text-center font-semibold">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][activity_division_id]"
                                            class="{{ $inputClass }} activity-division-select">

                                        <option value="">Select Division</option>

                                        @foreach($activityDivisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ (string) ($formItem['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][activity_id]"
                                            class="{{ $inputClass }} activity-select">

                                        <option value="">Select Activity</option>

                                        @foreach($activities as $activity)
                                            <option value="{{ $activity->id }}"
                                                    data-division="{{ $activity->activity_division_id }}"
                                                {{ (string) ($formItem['activity_id'] ?? '') === (string) $activity->id ? 'selected' : '' }}>
                                                {{ $activity->activity_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select class="{{ $inputClass }} material-group-select">
                                        <option value="">Select Group</option>

                                        @foreach($materialGroups as $group)
                                            <option value="{{ $group }}">
                                                {{ $group }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][material_type_id]"
                                            class="{{ $inputClass }} material-type-select"
                                            required>

                                        <option value="">Select Material Type</option>

                                        @foreach($materialTypes as $materialType)
                                            <option value="{{ $materialType->id }}"
                                                    data-group="{{ $materialType->material_group }}"
                                                    data-unit-id="{{ $materialType->unit_master_id }}"
                                                    data-unit-name="{{ optional($materialType->unit)->unit_name }}"
                                                {{ (string) ($formItem['material_type_id'] ?? '') === (string) $materialType->id ? 'selected' : '' }}>
                                                {{ $materialType->material_type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][brand_master_id]"
                                            class="{{ $inputClass }} brand-select">

                                        <option value="">Select Brand</option>

                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                    data-material-type="{{ $brand->material_type_id }}"
                                                {{ (string) ($formItem['brand_master_id'] ?? '') === (string) $brand->id ? 'selected' : '' }}>
                                                {{ $brand->brand_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][material_specification_id]"
                                            class="{{ $inputClass }} specification-select">

                                        <option value="">Select Specification</option>

                                        @foreach($specifications as $specification)
                                            <option value="{{ $specification->id }}"
                                                    data-material-type="{{ $specification->material_type_id }}"
                                                {{ (string) ($formItem['material_specification_id'] ?? '') === (string) $specification->id ? 'selected' : '' }}>
                                                {{ $specification->specification_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <select name="items[{{ $rowIndex }}][material_grade_id]"
                                            class="{{ $inputClass }} grade-select">

                                        <option value="">Select Grade / Rating</option>

                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}"
                                                    data-material-type="{{ $grade->material_type_id }}"
                                                {{ (string) ($formItem['material_grade_id'] ?? '') === (string) $grade->id ? 'selected' : '' }}>
                                                {{ $grade->grade_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-3">
                                    <input type="number"
                                           step="0.001"
                                           min="0.001"
                                           name="items[{{ $rowIndex }}][quantity_received]"
                                           value="{{ $formItem['quantity_received'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           required>
                                </td>

                                <td class="px-3 py-3">
                                    <input type="hidden"
                                           name="items[{{ $rowIndex }}][unit_master_id]"
                                           value="{{ $formItem['unit_master_id'] ?? '' }}"
                                           class="unit-id-input">

                                    <input type="text"
                                           value=""
                                           class="{{ $inputClass }} unit-name-input bg-gray-100"
                                           readonly
                                           placeholder="Auto">
                                </td>

                                <td class="px-3 py-3">
                                    <input type="text"
                                           name="items[{{ $rowIndex }}][remarks]"
                                           value="{{ $formItem['remarks'] ?? '' }}"
                                           class="{{ $inputClass }}"
                                           placeholder="Optional">
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <button type="button"
                                            class="remove-item-row rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                        Remove
                                    </button>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 p-5 text-sm text-gray-500">
                Brand, Specification, Grade/Rating and Unit are filtered automatically from the selected Material Type.
            </div>
        </div>


        {{-- Existing Photos --}}
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-2 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Existing Photos
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Review existing receipt photos. Tick Remove only for photos that should be deleted when this Draft is updated.
                    </p>
                </div>

                <div class="text-sm font-semibold text-gray-700">
                    {{ $materialReceived->photos->count() }} photo(s)
                </div>
            </div>

            @if($materialReceived->photos->isNotEmpty())

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">

                    @foreach($materialReceived->photos as $photo)

                        @php
                            $relatedItem = $photo->materialReceivedItem;
                            $relatedMaterial = $relatedItem?->materialType?->material_type_name;

                            $relatedVariant = collect([
                                $relatedItem?->brand?->brand_name,
                                $relatedItem?->specification?->specification_name,
                                $relatedItem?->grade?->grade_name,
                            ])->filter()->implode(' • ');
                        @endphp

                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

                            <a href="{{ $photo->file_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="block bg-gray-100">

                                <img src="{{ $photo->file_url }}"
                                     alt="{{ $photo->display_caption }}"
                                     loading="lazy"
                                     class="h-52 w-full object-cover">
                            </a>

                            <div class="p-4">

                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                        {{ $photo->photo_type }}
                                    </span>

                                    @if($photo->is_item_level)
                                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                            Item Specific
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                            Whole Receipt
                                        </span>
                                    @endif
                                </div>

                                @if($photo->caption)
                                    <p class="mb-3 font-semibold text-gray-800">
                                        {{ $photo->caption }}
                                    </p>
                                @endif

                                <div class="space-y-2 text-sm text-gray-700">

                                    <div>
                                        <span class="font-semibold">Material:</span>

                                        @if($relatedMaterial)
                                            {{ $relatedMaterial }}

                                            @if($relatedVariant)
                                                — {{ $relatedVariant }}
                                            @endif
                                        @else
                                            General / Whole Receipt
                                        @endif
                                    </div>

                                    <div>
                                        <span class="font-semibold">Uploaded:</span>
                                        {{ $photo->created_at?->format('d/m/Y h:i A') ?? '-' }}
                                    </div>

                                    <div class="break-all">
                                        <span class="font-semibold">File:</span>
                                        {{ $photo->original_name ?? basename($photo->file_path) }}
                                    </div>

                                </div>

                                <label class="mt-4 flex cursor-pointer items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2">
                                    <input type="checkbox"
                                           name="remove_photo_ids[]"
                                           value="{{ $photo->id }}"
                                           class="h-4 w-4 rounded border-red-300 text-red-600 focus:ring-red-500"
                                           {{ in_array((string) $photo->id, array_map('strval', old('remove_photo_ids', [])), true) ? 'checked' : '' }}>

                                    <span class="text-sm font-semibold text-red-700">
                                        Remove this photo on Update
                                    </span>
                                </label>

                            </div>
                        </div>

                    @endforeach

                </div>

            @else

                <div class="p-8 text-center text-sm text-gray-500">
                    No existing photos are attached to this receipt.
                </div>

            @endif

        </div>

        {{-- Add New Photos --}}
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">

                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Add New Photos
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Add supporting delivery, challan, invoice, unloading, condition or material-specific photos.
                    </p>
                </div>

                <button type="button"
                        id="add-photo-row"
                        class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">
                    + Add Photo
                </button>

            </div>

            <div id="photo-rows"
                 class="divide-y divide-gray-200">

                @foreach($oldPhotos as $photoIndex => $oldPhoto)

                    <div class="photo-row p-5"
                         data-photo-index="{{ $photoIndex }}">

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                            <div class="lg:col-span-2">
                                <label class="{{ $labelClass }}">
                                    Photo Type
                                </label>

                                <select name="photos[{{ $photoIndex }}][photo_type]"
                                        class="{{ $inputClass }} photo-type-select">

                                    @foreach($photoTypes as $photoType)
                                        <option value="{{ $photoType }}"
                                            {{ ($oldPhoto['photo_type'] ?? 'Material Photo') === $photoType ? 'selected' : '' }}>
                                            {{ $photoType }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-3">
                                <label class="{{ $labelClass }}">
                                    Material Item
                                </label>

                                <select name="photos[{{ $photoIndex }}][item_index]"
                                        class="{{ $inputClass }} photo-item-select"
                                        data-selected="{{ $oldPhoto['item_index'] ?? '' }}">

                                    <option value="">
                                        General / Whole Receipt
                                    </option>
                                </select>

                                <p class="mt-1 text-xs text-gray-500">
                                    Link the photo to a material only when it is item-specific.
                                </p>
                            </div>

                            <div class="lg:col-span-3">
                                <label class="{{ $labelClass }}">
                                    Caption
                                </label>

                                <input type="text"
                                       name="photos[{{ $photoIndex }}][caption]"
                                       value="{{ $oldPhoto['caption'] ?? '' }}"
                                       class="{{ $inputClass }}"
                                       maxlength="500"
                                       placeholder="Optional photo description">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="{{ $labelClass }}">
                                    Image
                                </label>

                                <input type="file"
                                       name="photos[{{ $photoIndex }}][file]"
                                       class="{{ $inputClass }} photo-file-input"
                                       accept="image/jpeg,image/png,image/webp,image/*">

                                <p class="mt-1 text-xs text-gray-500">
                                    JPG, PNG or WEBP. Maximum 10 MB.
                                </p>
                            </div>

                            <div class="lg:col-span-1 lg:pt-6">
                                <button type="button"
                                        class="remove-photo-row w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                    Remove
                                </button>
                            </div>

                        </div>

                        <div class="photo-preview-wrapper mt-4 hidden">
                            <div class="flex flex-wrap items-start gap-4 rounded-lg border border-gray-200 bg-gray-50 p-3">

                                <img src=""
                                     alt="Photo preview"
                                     class="photo-preview h-28 w-36 rounded-lg border border-gray-200 bg-white object-cover">

                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-800">Preview</p>
                                    <p class="photo-file-name mt-1"></p>
                                    <p class="photo-file-size mt-1 text-xs text-gray-500"></p>
                                </div>

                            </div>
                        </div>

                    </div>

                @endforeach

            </div>

            <div class="border-t border-gray-200 p-5 text-sm text-gray-500">
                Stored filename:
                <span class="font-semibold text-gray-700">
                    Project-Material-PhotoType-Engineer-YYYYMMDD-HHMMSS-Sequence.ext
                </span>
            </div>

        </div>


        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-7 py-3 font-semibold text-white hover:bg-blue-700">
                Update Material Receipt
            </button>

            <a href="{{ route('material-received.show', $materialReceived) }}"
               class="rounded-lg bg-gray-500 px-7 py-3 font-semibold text-white hover:bg-gray-600">
                Cancel
            </a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('material-items-body');
    const addRowButton = document.getElementById('add-item-row');
    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');
    const contractorCheckbox = document.getElementById('supplied_by_contractor');
    const contractorWrapper = document.getElementById('contractor-wrapper');
    const contractorSelect = document.getElementById('contractor_id');

    const photoRows = document.getElementById('photo-rows');
    const addPhotoRowButton = document.getElementById('add-photo-row');

    let rowIndex = body.querySelectorAll('.material-item-row').length;
    let photoIndex = photoRows.querySelectorAll('.photo-row').length;

    const activityOptions = @json($activityOptionsForJs);
    const materialTypeOptions = @json($materialTypeOptionsForJs);
    const brandOptions = @json($brandOptionsForJs);
    const specificationOptions = @json($specificationOptionsForJs);
    const gradeOptions = @json($gradeOptionsForJs);
    const materialGroups = @json($materialGroupsForJs);
    const photoTypes = @json($photoTypes);

    function makeOption(value, label, selected = false) {
        return new Option(label, value, selected, selected);
    }

    function rebuildSelect(select, placeholder, values, selectedValue = '') {
        select.innerHTML = '';
        select.add(makeOption('', placeholder));

        values.forEach(function (item) {
            select.add(
                makeOption(
                    String(item.id),
                    item.name,
                    String(item.id) === String(selectedValue)
                )
            );
        });
    }

    function initializeRow(row) {
        const divisionSelect = row.querySelector('.activity-division-select');
        const activitySelect = row.querySelector('.activity-select');
        const groupSelect = row.querySelector('.material-group-select');
        const typeSelect = row.querySelector('.material-type-select');
        const brandSelect = row.querySelector('.brand-select');
        const specificationSelect = row.querySelector('.specification-select');
        const gradeSelect = row.querySelector('.grade-select');
        const unitIdInput = row.querySelector('.unit-id-input');
        const unitNameInput = row.querySelector('.unit-name-input');

        let preservedActivityId = activitySelect.value;
        let preservedTypeId = typeSelect.value;
        let preservedBrandId = brandSelect.value;
        let preservedSpecificationId = specificationSelect.value;
        let preservedGradeId = gradeSelect.value;

        const selectedType = materialTypeOptions.find(function (type) {
            return String(type.id) === String(preservedTypeId);
        });

        if (selectedType) {
            groupSelect.value = selectedType.group || '';
        }

        function filterActivities(selectedValue = '') {
            const divisionId = divisionSelect.value;

            const filtered = activityOptions.filter(function (activity) {
                return divisionId === ''
                    || String(activity.division_id) === String(divisionId);
            });

            rebuildSelect(
                activitySelect,
                'Select Activity',
                filtered,
                selectedValue
            );
        }

        function filterMaterialTypes(selectedValue = '') {
            const group = groupSelect.value;

            const filtered = materialTypeOptions.filter(function (type) {
                return group === '' || type.group === group;
            });

            rebuildSelect(
                typeSelect,
                'Select Material Type',
                filtered,
                selectedValue
            );
        }

        function updateMaterialDependencies(options = {}) {
            const materialTypeId = typeSelect.value;

            const selectedMaterialType = materialTypeOptions.find(function (type) {
                return String(type.id) === String(materialTypeId);
            });

            unitIdInput.value = selectedMaterialType?.unit_id || '';
            unitNameInput.value = selectedMaterialType?.unit_name || '';

            const filteredBrands = brandOptions.filter(function (brand) {
                return String(brand.material_type_id) === String(materialTypeId);
            });

            const filteredSpecifications = specificationOptions.filter(function (specification) {
                return String(specification.material_type_id) === String(materialTypeId);
            });

            const filteredGrades = gradeOptions.filter(function (grade) {
                return String(grade.material_type_id) === String(materialTypeId);
            });

            rebuildSelect(
                brandSelect,
                'Select Brand',
                filteredBrands,
                options.brandId || ''
            );

            rebuildSelect(
                specificationSelect,
                'Select Specification',
                filteredSpecifications,
                options.specificationId || ''
            );

            rebuildSelect(
                gradeSelect,
                'Select Grade / Rating',
                filteredGrades,
                options.gradeId || ''
            );
        }

        divisionSelect.addEventListener('change', function () {
            filterActivities('');
        });

        groupSelect.addEventListener('change', function () {
            filterMaterialTypes('');
            updateMaterialDependencies();
            refreshPhotoItemOptions();
        });

        typeSelect.addEventListener('change', function () {
            updateMaterialDependencies();
            refreshPhotoItemOptions();
        });

        row.querySelector('.remove-item-row').addEventListener('click', function () {
            const rows = body.querySelectorAll('.material-item-row');

            if (rows.length <= 1) {
                alert('At least one material row is required.');
                return;
            }

            row.remove();
            renumberMaterialRows();
            refreshPhotoItemOptions();
        });

        filterActivities(preservedActivityId);
        filterMaterialTypes(preservedTypeId);

        updateMaterialDependencies({
            brandId: preservedBrandId,
            specificationId: preservedSpecificationId,
            gradeId: preservedGradeId,
        });
    }

    function buildNewRow(index) {
        const row = document.createElement('tr');

        row.className = 'material-item-row align-top';
        row.dataset.rowIndex = index;

        row.innerHTML = `
            <td class="row-number px-3 py-3 text-center font-semibold"></td>

            <td class="px-3 py-3">
                <select name="items[${index}][activity_division_id]"
                        class="{{ $inputClass }} activity-division-select">
                    <option value="">Select Division</option>
                    @foreach($activityDivisions as $division)
                        <option value="{{ $division->id }}">
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][activity_id]"
                        class="{{ $inputClass }} activity-select">
                    <option value="">Select Activity</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select class="{{ $inputClass }} material-group-select">
                    <option value="">Select Group</option>
                    ${materialGroups.map(function (group) {
                        return `<option value="${escapeHtml(group)}">${escapeHtml(group)}</option>`;
                    }).join('')}
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][material_type_id]"
                        class="{{ $inputClass }} material-type-select"
                        required>
                    <option value="">Select Material Type</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][brand_master_id]"
                        class="{{ $inputClass }} brand-select">
                    <option value="">Select Brand</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][material_specification_id]"
                        class="{{ $inputClass }} specification-select">
                    <option value="">Select Specification</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <select name="items[${index}][material_grade_id]"
                        class="{{ $inputClass }} grade-select">
                    <option value="">Select Grade / Rating</option>
                </select>
            </td>

            <td class="px-3 py-3">
                <input type="number"
                       step="0.001"
                       min="0.001"
                       name="items[${index}][quantity_received]"
                       class="{{ $inputClass }}"
                       required>
            </td>

            <td class="px-3 py-3">
                <input type="hidden"
                       name="items[${index}][unit_master_id]"
                       class="unit-id-input">

                <input type="text"
                       class="{{ $inputClass }} unit-name-input bg-gray-100"
                       readonly
                       placeholder="Auto">
            </td>

            <td class="px-3 py-3">
                <input type="text"
                       name="items[${index}][remarks]"
                       class="{{ $inputClass }}"
                       placeholder="Optional">
            </td>

            <td class="px-3 py-3 text-center">
                <button type="button"
                        class="remove-item-row rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                    Remove
                </button>
            </td>
        `;

        return row;
    }

    function refreshRowNumbers() {
        body.querySelectorAll('.material-item-row').forEach(function (row, index) {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    function renumberMaterialRows() {
        body.querySelectorAll('.material-item-row')
            .forEach(function (row, index) {
                row.dataset.rowIndex = index;
                row.querySelector('.row-number').textContent = index + 1;

                row.querySelectorAll('[name^="items["]')
                    .forEach(function (field) {
                        field.name = field.name.replace(
                            /^items\[\d+\]/,
                            `items[${index}]`
                        );
                    });
            });

        rowIndex = body.querySelectorAll('.material-item-row').length;
    }

    function materialItemOptions() {
        return Array.from(body.querySelectorAll('.material-item-row'))
            .map(function (row, index) {
                const typeSelect = row.querySelector('.material-type-select');
                const selectedOption = typeSelect.options[typeSelect.selectedIndex];

                return {
                    value: String(index),
                    label: typeSelect.value
                        ? `Item ${index + 1} — ${selectedOption?.text || 'Material'}`
                        : `Item ${index + 1} — Material not selected`,
                };
            });
    }

    function refreshPhotoItemOptions() {
        const options = materialItemOptions();

        photoRows.querySelectorAll('.photo-item-select')
            .forEach(function (select) {
                const selected = select.value !== ''
                    ? select.value
                    : (select.dataset.selected || '');

                select.innerHTML = '';
                select.add(new Option('General / Whole Receipt', ''));

                options.forEach(function (item) {
                    select.add(
                        new Option(
                            item.label,
                            item.value,
                            false,
                            String(item.value) === String(selected)
                        )
                    );
                });

                select.dataset.selected = select.value;
            });
    }

    function initializePhotoRow(row) {
        const fileInput = row.querySelector('.photo-file-input');
        const previewWrapper = row.querySelector('.photo-preview-wrapper');
        const previewImage = row.querySelector('.photo-preview');
        const fileName = row.querySelector('.photo-file-name');
        const fileSize = row.querySelector('.photo-file-size');
        const itemSelect = row.querySelector('.photo-item-select');

        itemSelect.addEventListener('change', function () {
            itemSelect.dataset.selected = itemSelect.value;
        });

        fileInput.addEventListener('change', function () {
            const file = fileInput.files?.[0];

            if (!file) {
                previewWrapper.classList.add('hidden');
                previewImage.removeAttribute('src');
                fileName.textContent = '';
                fileSize.textContent = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('Each photo must be 10 MB or smaller.');
                fileInput.value = '';
                previewWrapper.classList.add('hidden');
                return;
            }

            const objectUrl = URL.createObjectURL(file);

            previewImage.src = objectUrl;
            fileName.textContent = file.name;
            fileSize.textContent =
                `${(file.size / 1024 / 1024).toFixed(2)} MB`;

            previewWrapper.classList.remove('hidden');

            previewImage.onload = function () {
                URL.revokeObjectURL(objectUrl);
            };
        });

        row.querySelector('.remove-photo-row')
            .addEventListener('click', function () {
                const rows = photoRows.querySelectorAll('.photo-row');

                if (rows.length <= 1) {
                    fileInput.value = '';
                    row.querySelector('.photo-type-select').value =
                        'Material Photo';

                    row.querySelector('input[type="text"]').value = '';
                    itemSelect.value = '';
                    itemSelect.dataset.selected = '';

                    previewWrapper.classList.add('hidden');
                    return;
                }

                row.remove();
                renumberPhotoRows();
            });
    }

    function buildPhotoRow(index) {
        const row = document.createElement('div');

        row.className = 'photo-row p-5';
        row.dataset.photoIndex = index;

        row.innerHTML = `
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                <div class="lg:col-span-2">
                    <label class="{{ $labelClass }}">Photo Type</label>

                    <select name="photos[${index}][photo_type]"
                            class="{{ $inputClass }} photo-type-select">
                        ${photoTypes.map(function (type) {
                            return `<option value="${escapeHtml(type)}">${escapeHtml(type)}</option>`;
                        }).join('')}
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelClass }}">Material Item</label>

                    <select name="photos[${index}][item_index]"
                            class="{{ $inputClass }} photo-item-select"
                            data-selected="">
                        <option value="">General / Whole Receipt</option>
                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        Link the photo to a material only when it is item-specific.
                    </p>
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelClass }}">Caption</label>

                    <input type="text"
                           name="photos[${index}][caption]"
                           class="{{ $inputClass }}"
                           maxlength="500"
                           placeholder="Optional photo description">
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelClass }}">Image</label>

                    <input type="file"
                           name="photos[${index}][file]"
                           class="{{ $inputClass }} photo-file-input"
                           accept="image/jpeg,image/png,image/webp,image/*">

                    <p class="mt-1 text-xs text-gray-500">
                        JPG, PNG or WEBP. Maximum 10 MB.
                    </p>
                </div>

                <div class="lg:col-span-1 lg:pt-6">
                    <button type="button"
                            class="remove-photo-row w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Remove
                    </button>
                </div>

            </div>

            <div class="photo-preview-wrapper mt-4 hidden">
                <div class="flex flex-wrap items-start gap-4 rounded-lg border border-gray-200 bg-gray-50 p-3">

                    <img src=""
                         alt="Photo preview"
                         class="photo-preview h-28 w-36 rounded-lg border border-gray-200 bg-white object-cover">

                    <div class="text-sm text-gray-600">
                        <p class="font-semibold text-gray-800">Preview</p>
                        <p class="photo-file-name mt-1"></p>
                        <p class="photo-file-size mt-1 text-xs text-gray-500"></p>
                    </div>

                </div>
            </div>
        `;

        return row;
    }

    function renumberPhotoRows() {
        photoRows.querySelectorAll('.photo-row')
            .forEach(function (row, index) {
                row.dataset.photoIndex = index;

                row.querySelectorAll('[name^="photos["]')
                    .forEach(function (field) {
                        field.name = field.name.replace(
                            /^photos\[\d+\]/,
                            `photos[${index}]`
                        );
                    });
            });

        photoIndex = photoRows.querySelectorAll('.photo-row').length;
        refreshPhotoItemOptions();
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function cloneOptions(select) {
        return Array.from(select.options).map(function (option) {
            return option.cloneNode(true);
        });
    }

    const originalBlockOptions = cloneOptions(blockSelect);
    const originalFloorOptions = cloneOptions(floorSelect);
    const originalUnitOptions = cloneOptions(unitSelect);

    function filterLocationSelect(select, source, predicate, placeholder) {
        const currentValue = select.value;

        select.innerHTML = '';
        select.add(new Option(placeholder, ''));

        source.forEach(function (option) {
            if (option.value !== '' && predicate(option)) {
                const clonedOption = option.cloneNode(true);

                if (String(clonedOption.value) === String(currentValue)) {
                    clonedOption.selected = true;
                }

                select.add(clonedOption);
            }
        });
    }

    function filterProjectLocations() {
        const projectId = projectSelect.value;

        filterLocationSelect(
            blockSelect,
            originalBlockOptions,
            function (option) {
                return projectId === ''
                    || String(option.dataset.project) === String(projectId);
            },
            'Select Block'
        );

        filterFloors();
    }

    function filterFloors() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;

        filterLocationSelect(
            floorSelect,
            originalFloorOptions,
            function (option) {
                const projectMatch = projectId === ''
                    || String(option.dataset.project) === String(projectId);

                const blockMatch = blockId === ''
                    || String(option.dataset.block) === String(blockId);

                return projectMatch && blockMatch;
            },
            'Select Floor'
        );

        filterUnits();
    }

    function filterUnits() {
        const projectId = projectSelect.value;
        const blockId = blockSelect.value;
        const floorId = floorSelect.value;

        filterLocationSelect(
            unitSelect,
            originalUnitOptions,
            function (option) {
                const projectMatch = projectId === ''
                    || String(option.dataset.project) === String(projectId);

                const blockMatch = blockId === ''
                    || String(option.dataset.block) === String(blockId);

                const floorMatch = floorId === ''
                    || String(option.dataset.floor) === String(floorId);

                return projectMatch && blockMatch && floorMatch;
            },
            'Select Unit'
        );
    }

    function toggleContractor() {
        contractorWrapper.classList.toggle(
            'hidden',
            !contractorCheckbox.checked
        );

        if (!contractorCheckbox.checked) {
            contractorSelect.value = '';
        }
    }

    addRowButton.addEventListener('click', function () {
        const newRow = buildNewRow(rowIndex++);
        body.appendChild(newRow);
        initializeRow(newRow);
        renumberMaterialRows();
        refreshPhotoItemOptions();
    });

    addPhotoRowButton.addEventListener('click', function () {
        const newPhotoRow = buildPhotoRow(photoIndex++);
        photoRows.appendChild(newPhotoRow);
        initializePhotoRow(newPhotoRow);
        renumberPhotoRows();
    });

    projectSelect.addEventListener('change', function () {
        blockSelect.value = '';
        floorSelect.value = '';
        unitSelect.value = '';
        filterProjectLocations();
    });

    blockSelect.addEventListener('change', function () {
        floorSelect.value = '';
        unitSelect.value = '';
        filterFloors();
    });

    floorSelect.addEventListener('change', function () {
        unitSelect.value = '';
        filterUnits();
    });

    contractorCheckbox.addEventListener('change', toggleContractor);

    body.querySelectorAll('.material-item-row').forEach(initializeRow);

    photoRows.querySelectorAll('.photo-row')
        .forEach(initializePhotoRow);

    renumberMaterialRows();
    renumberPhotoRows();

    filterProjectLocations();
    toggleContractor();
    refreshPhotoItemOptions();
});
</script>

@endsection
