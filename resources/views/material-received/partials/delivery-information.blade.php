{{-- Simple delivery header: receipt logistics only --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
    <div class="mb-5 rounded-xl bg-[#10212F] px-4 py-3 text-white">
        <h2 class="text-lg font-bold sm:text-xl">Delivery Information</h2>
        <p class="mt-1 text-xs text-slate-200">Project, delivery and supplier details for this receipt.</p>
    </div>


    <div class="mb-5 grid grid-cols-1 gap-4 rounded-xl border border-blue-200 bg-blue-50 p-4 md:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">Receipt Source <span class="text-red-500">*</span></label>
            <select name="receipt_source" id="receipt_source" class="{{ $inputClass }}" required {{ !empty($selectedPurchaseOrder) ? 'disabled' : '' }}>
                <option value="DIRECT" {{ old('receipt_source', $materialReceived->receipt_source ?? (!empty($selectedPurchaseOrder) ? 'PO' : 'DIRECT')) === 'DIRECT' ? 'selected' : '' }}>Direct / Unplanned Receipt</option>
                <option value="PO" {{ old('receipt_source', $materialReceived->receipt_source ?? (!empty($selectedPurchaseOrder) ? 'PO' : 'DIRECT')) === 'PO' ? 'selected' : '' }}>Against Purchase Order</option>
            </select>
            @if(!empty($selectedPurchaseOrder))<input type="hidden" name="receipt_source" value="PO">@endif
        </div>
        <div id="purchase-order-source-wrap" class="{{ old('receipt_source', $materialReceived->receipt_source ?? (!empty($selectedPurchaseOrder) ? 'PO' : 'DIRECT')) === 'PO' ? '' : 'hidden' }}">
            <label class="{{ $labelClass }}">Purchase Order</label>
            <div class="flex gap-2">
                <select name="purchase_order_id" id="purchase_order_id" class="{{ $inputClass }}" {{ !empty($selectedPurchaseOrder) ? 'disabled' : '' }}>
                    <option value="">Select Issued PO</option>
                    @foreach($receivablePurchaseOrders as $po)
                        <option value="{{ $po->id }}" {{ (string) old('purchase_order_id', $materialReceived->purchase_order_id ?? $selectedPurchaseOrder?->id ?? '') === (string) $po->id ? 'selected' : '' }}>
                            {{ $po->po_number }} — {{ $po->po_date ? \Carbon\Carbon::parse($po->po_date)->format('d M Y') : 'No Date' }} — {{ $po->vendor_name }} — {{ $po->project_name }}
                        </option>
                    @endforeach
                </select>
                @if(!empty($selectedPurchaseOrder))<input type="hidden" name="purchase_order_id" value="{{ $selectedPurchaseOrder->id }}">@endif
                @if(!isset($materialReceived) && empty($selectedPurchaseOrder))
                    <button type="button" id="load-purchase-order" class="shrink-0 rounded-lg bg-[#10212F] px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Load PO</button>
                @endif
            </div>
            <p class="mt-1 text-xs text-blue-700">Load the PO to bring its pending items into this receipt. Partial receipts are allowed.</p>
        </div>
    </div>

    @if(!empty($selectedPurchaseOrder))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            <strong>{{ $selectedPurchaseOrder->po_number }}</strong> · {{ $selectedPurchaseOrder->vendor_name }} · {{ $selectedPurchaseOrder->project_name }}
            <span class="ml-2">Status: {{ $selectedPurchaseOrder->status }}</span>
        </div>
    @endif

    <section class="mb-5 overflow-hidden rounded-xl border border-gray-200 bg-white last:mb-0">
        <div class="border-b border-blue-100 bg-blue-50 px-4 py-3">
            <h3 class="text-sm font-bold text-gray-900">Receipt details</h3>
            <p class="mt-0.5 text-xs text-gray-600">Identify the receipt, project and receiving person.</p>
        </div>
        <div class="grid grid-cols-1 gap-x-4 gap-y-4 p-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                        <label class="{{ $labelClass }}">Project <span class="text-red-500">*</span></label>
                        <select name="project_id" id="project_id" class="{{ $inputClass }}" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ (string) old('project_id', $materialReceived->project_id ?? $selectedPurchaseOrder?->project_id ?? '') === (string) $project->id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Received Date <span class="text-red-500">*</span></label>
                        <input type="date" name="received_date"
                               value="{{ old('received_date', isset($materialReceived) ? optional($materialReceived->received_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                               class="{{ $inputClass }}" required>
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Received By</label>
                        <input type="text" value="{{ auth()->user()->name ?? 'Current User' }}" class="{{ $inputClass }} bg-gray-50" readonly>
                    </div>
        </div>
    </section>

    <details class="mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white" {{ $errors->has('vendor_id') || $errors->has('vehicle_number') || $errors->has('driver_name') || $errors->has('challan_number') || $errors->has('bill_number') ? 'open' : '' }}>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-gray-50 px-4 py-3 marker:hidden">
            <span><span class="block text-sm font-bold text-gray-900">Supplier & delivery</span><span class="mt-0.5 block text-xs text-gray-600">Vendor, vehicle, driver, challan and bill · click to expand</span></span>
            <span class="text-sm font-semibold text-slate-600">Expand / Collapse ▾</span>
        </summary>
        <div class="grid grid-cols-1 gap-x-4 gap-y-4 p-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                        <label class="{{ $labelClass }}">Vendor / Supplier</label>
                        <select name="vendor_id" class="{{ $inputClass }}">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ (string) old('vendor_id', $materialReceived->vendor_id ?? $selectedPurchaseOrder?->vendor_id ?? '') === (string) $vendor->id ? 'selected' : '' }}>{{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Vehicle Number</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $materialReceived->vehicle_number ?? '') }}" class="{{ $inputClass }}" placeholder="TS09AB1234">
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Driver Name</label>
                        <input type="text" name="driver_name" value="{{ old('driver_name', $materialReceived->driver_name ?? '') }}" class="{{ $inputClass }}">
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Challan Number</label>
                        <input type="text" name="challan_number" value="{{ old('challan_number', $materialReceived->challan_number ?? '') }}" class="{{ $inputClass }}">
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Bill Number</label>
                        <input type="text" name="bill_number" value="{{ old('bill_number', $materialReceived->bill_number ?? '') }}" class="{{ $inputClass }}">
                    </div>
        </div>
    </details>

    <details class="mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white" {{ $errors->has('project_block_id') || $errors->has('project_floor_id') || $errors->has('project_unit_id') || $errors->has('storage_location') || $errors->has('remarks') ? 'open' : '' }}>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-gray-50 px-4 py-3 marker:hidden">
            <span><span class="block text-sm font-bold text-gray-900">Site location & notes</span><span class="mt-0.5 block text-xs text-gray-600">Block, floor, unit, storage and notes · click to expand</span></span>
            <span class="text-sm font-semibold text-slate-600">Expand / Collapse ▾</span>
        </summary>
        <div class="grid grid-cols-1 gap-x-4 gap-y-4 p-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                        <label class="{{ $labelClass }}">Block</label>
                        <select name="project_block_id" id="project_block_id" class="{{ $inputClass }}">
                            <option value="">Select Block</option>
                            @foreach($projectBlocks as $block)
                                <option value="{{ $block->id }}" data-project="{{ $block->project_id }}"
                                    {{ (string) old('project_block_id', $materialReceived->project_block_id ?? '') === (string) $block->id ? 'selected' : '' }}>{{ $block->name }}</option>
                            @endforeach
                        </select>
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Floor</label>
                        <select name="project_floor_id" id="project_floor_id" class="{{ $inputClass }}">
                            <option value="">Select Floor</option>
                            @foreach($projectFloors as $floor)
                                <option value="{{ $floor->id }}" data-project="{{ $floor->project_id }}" data-block="{{ $floor->project_block_id }}"
                                    {{ (string) old('project_floor_id', $materialReceived->project_floor_id ?? '') === (string) $floor->id ? 'selected' : '' }}>{{ $floor->name }}</option>
                            @endforeach
                        </select>
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Unit</label>
                        <select name="project_unit_id" id="project_unit_id" class="{{ $inputClass }}">
                            <option value="">Select Unit</option>
                            @foreach($projectUnits as $projectUnit)
                                <option value="{{ $projectUnit->id }}" data-project="{{ $projectUnit->project_id }}" data-block="{{ $projectUnit->project_block_id }}" data-floor="{{ $projectUnit->project_floor_id }}"
                                    {{ (string) old('project_unit_id', $materialReceived->project_unit_id ?? '') === (string) $projectUnit->id ? 'selected' : '' }}>{{ $projectUnit->name }}</option>
                            @endforeach
                        </select>
                    </div>

            <div>
                        <label class="{{ $labelClass }}">Storage Location</label>
                        <input type="text" name="storage_location" value="{{ old('storage_location', $materialReceived->storage_location ?? '') }}" class="{{ $inputClass }}" placeholder="Site Store / Yard / Floor">
                    </div>

            <div class="sm:col-span-2 xl:col-span-4">
                        <label class="{{ $labelClass }}">Delivery Remarks</label>
                        <textarea name="remarks" rows="2" class="{{ $inputClass }}" placeholder="General notes for this delivery">{{ old('remarks', $materialReceived->remarks ?? '') }}</textarea>
                    </div>
        </div>
    </details>

</div>
