@php
    $isEdit = isset($materialDispatch);

    $existingItems = $isEdit
        ? $materialDispatch->items->map(function ($item) {
            $allocation = $item->allocations->first();

            return [
                'id' => $item->id,
                'material_requirement_item_id' => $allocation?->material_requirement_item_id,
                'material_type_id' => $item->material_type_id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'specification_text' => $item->specification_text,
                'brand_master_id' => $item->brand_master_id,
                'brand_name' => $item->brand_name,
                'unit_master_id' => $item->unit_master_id,
                'unit_name' => $item->unit_name,
                'dispatched_quantity' => $item->dispatched_quantity,
                'remarks' => $item->remarks,
                'source_label' => $allocation?->material_requirement_item_id
                    ? 'Material Requirement'
                    : 'Direct HO Dispatch',
            ];
        })->values()->all()
        : [];

    $formItems = old('items', $existingItems);

    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-700';

    $selectedProjectId = old(
        'project_id',
        $isEdit ? $materialDispatch->project_id : ''
    );
@endphp

@if(session('error'))
    <div class="mb-5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-700">
        <p class="mb-2 font-semibold">Please correct the following:</p>
        <ul class="ml-5 list-disc space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $isEdit
          ? route('material-dispatches.update', $materialDispatch)
          : route('material-dispatches.store') }}"
      id="material-dispatch-form"
      data-requirements-url="{{ route('material-dispatches.approved-requirement-items') }}"
      data-dependency-url-template="{{ route('materials.product-dependencies', ['materialType' => '__PRODUCT__']) }}">

    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-gray-800">Dispatch Information</h2>
            <p class="mt-1 text-xs text-gray-500">Project, dispatch date and destination details.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label for="project_id" class="{{ $labelClass }}">
                    Project <span class="text-red-500">*</span>
                </label>
                <select name="project_id" id="project_id" class="{{ $inputClass }}" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                                data-location="{{ $project->location }}"
                            {{ (string) $selectedProjectId === (string) $project->id ? 'selected' : '' }}>
                            {{ $project->project_name }}
                            @if($project->project_code)
                                — {{ $project->project_code }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="dispatch_date" class="{{ $labelClass }}">
                    Dispatch Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       name="dispatch_date"
                       id="dispatch_date"
                       value="{{ old('dispatch_date', $isEdit ? $materialDispatch->dispatch_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                       class="{{ $inputClass }}"
                       required>
            </div>

            <div>
                <label for="expected_delivery_date" class="{{ $labelClass }}">Expected Delivery</label>
                <input type="date"
                       name="expected_delivery_date"
                       id="expected_delivery_date"
                       value="{{ old('expected_delivery_date', $isEdit ? $materialDispatch->expected_delivery_date?->format('Y-m-d') : '') }}"
                       class="{{ $inputClass }}">
            </div>

            <div class="xl:col-span-2">
                <label for="dispatch_from" class="{{ $labelClass }}">
                    Dispatch From <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="dispatch_from"
                       id="dispatch_from"
                       value="{{ old('dispatch_from', $isEdit ? $materialDispatch->dispatch_from : $defaultDispatchFrom) }}"
                       class="{{ $inputClass }}"
                       required>
            </div>

            <div class="xl:col-span-2">
                <label for="dispatch_from_address" class="{{ $labelClass }}">Dispatch From Address</label>
                <input type="text"
                       name="dispatch_from_address"
                       id="dispatch_from_address"
                       value="{{ old('dispatch_from_address', $isEdit ? $materialDispatch->dispatch_from_address : '') }}"
                       class="{{ $inputClass }}"
                       placeholder="Optional Head Office / store address">
            </div>

            <div class="md:col-span-2 xl:col-span-4">
                <label for="delivery_address" class="{{ $labelClass }}">Delivery Address</label>
                <textarea name="delivery_address"
                          id="delivery_address"
                          rows="2"
                          class="{{ $inputClass }}"
                          placeholder="Project / site delivery address">{{ old('delivery_address', $isEdit ? $materialDispatch->delivery_address : '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Against Approved Material Requirement</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Add material available for HO dispatch after PO and previous HO allocations.
                </p>
            </div>
            <button type="button"
                    id="refresh-requirements"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Load Requirements
            </button>
        </div>

        <div id="requirements-message" class="px-5 py-5 text-sm text-gray-500">
            Select a Project, then click <strong>Load Requirements</strong>.
        </div>
        <div id="requirements-list" class="hidden divide-y divide-gray-200"></div>
    </div>

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Direct / Unplanned Head Office Dispatch</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Use when material is being sent without an approved Material Requirement.
                </p>
            </div>
            <div id="entry-mode-badge"
                 class="hidden rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                Editing Item
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-3">
                <x-rds.product-selector
                    name="entry_product_id"
                    label="Product"
                    placeholder="Search Product..."
                />
            </div>

            <div class="lg:col-span-2">
                <label for="entry_specification" class="{{ $labelClass }}">Specification / Size</label>
                <input type="text"
                       id="entry_specification"
                       class="{{ $inputClass }}"
                       placeholder='120 mm, 6", 6A, SN4'>
            </div>

            <div class="lg:col-span-2">
                <label for="entry_brand" class="{{ $labelClass }}">Brand</label>
                <select id="entry_brand" class="{{ $inputClass }}">
                    <option value="">Any / Blank</option>
                </select>
            </div>

            <div class="lg:col-span-1">
                <label for="entry_quantity" class="{{ $labelClass }}">Qty</label>
                <input type="number"
                       id="entry_quantity"
                       min="0.001"
                       step="0.001"
                       class="{{ $inputClass }} text-right"
                       placeholder="0">
            </div>

            <div class="lg:col-span-2">
                <label for="entry_unit" class="{{ $labelClass }}">Unit</label>
                <select id="entry_unit" class="{{ $inputClass }}">
                    <option value="">Select Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">
                            {{ $unit->unit_name }}
                            @if($unit->unit_code)
                                ({{ $unit->unit_code }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="entry_remarks" class="{{ $labelClass }}">Remarks</label>
                <input type="text"
                       id="entry_remarks"
                       class="{{ $inputClass }}"
                       placeholder="Optional">
            </div>
        </div>

        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <button type="button"
                    id="cancel-item-edit"
                    class="hidden rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel Edit
            </button>
            <button type="button"
                    id="add-direct-item"
                    class="rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                + Add Direct Item
            </button>
        </div>
    </div>

    <div class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Dispatch Items</h2>
                <p class="mt-1 text-xs text-gray-500">Final material sheet to be sent to the project site.</p>
            </div>
            <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                <span id="dispatch-item-count">0</span> Item(s)
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1250px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="w-12 px-3 py-3 text-center">#</th>
                        <th class="min-w-[250px] px-3 py-3 text-left">Product</th>
                        <th class="min-w-[180px] px-3 py-3 text-left">Specification / Size</th>
                        <th class="min-w-[140px] px-3 py-3 text-left">Brand</th>
                        <th class="w-28 px-3 py-3 text-right">Qty</th>
                        <th class="w-28 px-3 py-3 text-left">Unit</th>
                        <th class="min-w-[180px] px-3 py-3 text-left">Source</th>
                        <th class="min-w-[180px] px-3 py-3 text-left">Remarks</th>
                        <th class="w-28 px-3 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="dispatch-items-body" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <div id="empty-dispatch-state" class="px-6 py-10 text-center text-sm text-gray-500">
            No material added to this dispatch yet.
        </div>
    </div>

    <div id="hidden-item-inputs"></div>

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-gray-800">Transport / Delivery Details</h2>
            <p class="mt-1 text-xs text-gray-500">Optional transport, vehicle and challan information.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="transport_mode" class="{{ $labelClass }}">Transport Mode</label>
                <input type="text" name="transport_mode" id="transport_mode"
                       value="{{ old('transport_mode', $isEdit ? $materialDispatch->transport_mode : '') }}"
                       class="{{ $inputClass }}" placeholder="Company Vehicle / Transporter">
            </div>

            <div>
                <label for="vehicle_number" class="{{ $labelClass }}">Vehicle Number</label>
                <input type="text" name="vehicle_number" id="vehicle_number"
                       value="{{ old('vehicle_number', $isEdit ? $materialDispatch->vehicle_number : '') }}"
                       class="{{ $inputClass }}" placeholder="TS 00 AB 0000">
            </div>

            <div>
                <label for="driver_name" class="{{ $labelClass }}">Driver Name</label>
                <input type="text" name="driver_name" id="driver_name"
                       value="{{ old('driver_name', $isEdit ? $materialDispatch->driver_name : '') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label for="driver_mobile" class="{{ $labelClass }}">Driver Mobile</label>
                <input type="text" name="driver_mobile" id="driver_mobile"
                       value="{{ old('driver_mobile', $isEdit ? $materialDispatch->driver_mobile : '') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label for="transporter_name" class="{{ $labelClass }}">Transporter</label>
                <input type="text" name="transporter_name" id="transporter_name"
                       value="{{ old('transporter_name', $isEdit ? $materialDispatch->transporter_name : '') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label for="challan_number" class="{{ $labelClass }}">Challan Number</label>
                <input type="text" name="challan_number" id="challan_number"
                       value="{{ old('challan_number', $isEdit ? $materialDispatch->challan_number : '') }}"
                       class="{{ $inputClass }}">
            </div>

            <div>
                <label for="reference_number" class="{{ $labelClass }}">Reference Number</label>
                <input type="text" name="reference_number" id="reference_number"
                       value="{{ old('reference_number', $isEdit ? $materialDispatch->reference_number : '') }}"
                       class="{{ $inputClass }}">
            </div>
        </div>
    </div>

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-gray-800">Notes</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="dispatch_notes" class="{{ $labelClass }}">Dispatch Notes</label>
                <textarea name="dispatch_notes" id="dispatch_notes" rows="3"
                          class="{{ $inputClass }}"
                          placeholder="Instructions visible for this dispatch">{{ old('dispatch_notes', $isEdit ? $materialDispatch->dispatch_notes : '') }}</textarea>
            </div>

            <div>
                <label for="internal_remarks" class="{{ $labelClass }}">Internal Remarks</label>
                <textarea name="internal_remarks" id="internal_remarks" rows="3"
                          class="{{ $inputClass }}"
                          placeholder="Internal ERP remarks">{{ old('internal_remarks', $isEdit ? $materialDispatch->internal_remarks : '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="sticky bottom-0 z-20 -mx-4 border-t border-gray-200 bg-white/95 px-4 py-3 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur sm:-mx-6 sm:px-6 lg:mx-0 lg:rounded-xl lg:border">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-500">
                <span class="font-semibold text-gray-800" id="sticky-item-count">0</span>
                material item(s) ready for dispatch.
            </div>

            <div class="flex gap-3">
                <a href="{{ $isEdit
                        ? route('material-dispatches.show', $materialDispatch)
                        : route('material-dispatches.index') }}"
                   class="flex-1 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">
                    Cancel
                </a>

                <button type="submit"
                        class="flex-1 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 sm:flex-none">
                    {{ $isEdit ? 'Update Draft' : 'Save Draft Dispatch' }}
                </button>
            </div>
        </div>
    </div>
</form>

@include('material-dispatches.partials.javascript', [
    'formItems' => $formItems,
])
