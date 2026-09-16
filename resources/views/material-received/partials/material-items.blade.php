{{-- Material Received V2: Direct or Purchase Order receipt --}}
@php $isPoReceipt = old('receipt_source', $materialReceived->receipt_source ?? (!empty($selectedPurchaseOrder) ? 'PO' : 'DIRECT')) === 'PO'; @endphp
<div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-gray-200 bg-[#10212F] p-4 text-white md:flex-row md:items-center md:justify-between md:p-5">
        <div>
            <h2 class="text-lg font-bold sm:text-xl">Material Items</h2>
            <p class="mt-1 text-xs text-slate-200 sm:text-sm">
                {{ $isPoReceipt ? 'Record today’s physical arrival against the Purchase Order. Accepted + Damaged + Rejected must equal Receive Now.' : 'Enter what physically arrived. Product Master unit is a default and may be changed for this transaction.' }}
            </p>
        </div>
        @if(!$isPoReceipt)
            <button type="button" id="add-item-row" class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700 md:w-auto md:py-2">+ Add Row</button>
        @else
            <button type="button" id="add-item-row" class="hidden">+ Add Row</button>
        @endif
    </div>

    @if(!$isPoReceipt)
        <div class="border-b border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800">
            Product not found? Use <strong>+ Material Not Found</strong>. The receipt continues and the item goes to Pending Classification.
        </div>
    @else
        <div class="border-b border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-900">
            <strong>PO quantity rule:</strong> Receive Now + Short cannot exceed Pending. Only Accepted quantity will fulfil the Material Requirement and later enter stock.
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-[1850px] w-full text-sm">
            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-3 py-3 text-center">#</th>
                    <th class="min-w-[260px] px-3 py-3 text-left">Product</th>
                    <th class="min-w-48 px-3 py-3 text-left">Specification / Grade</th>
                    <th class="min-w-40 px-3 py-3 text-left">Brand</th>
                    @if($isPoReceipt)
                        <th class="px-3 py-3 text-right">Ordered</th>
                        <th class="px-3 py-3 text-right">Previously Accounted</th>
                        <th class="px-3 py-3 text-right">Pending</th>
                    @endif
                    <th class="px-3 py-3 text-right">Receive Now</th>
                    <th class="px-3 py-3 text-left">Unit</th>
                    <th class="px-3 py-3 text-right text-green-700">Accepted</th>
                    <th class="px-3 py-3 text-right text-yellow-700">Short</th>
                    <th class="px-3 py-3 text-right text-orange-700">Damaged</th>
                    <th class="px-3 py-3 text-right text-red-700">Rejected</th>
                    <th class="min-w-48 px-3 py-3 text-left">Purpose / Remarks</th>
                    <th class="px-3 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody id="material-items-body" class="divide-y divide-gray-200">
                @foreach($oldItems as $rowIndex => $oldItem)
                    @php $entryMode = $oldItem['entry_mode'] ?? 'existing'; @endphp
                    <tr class="material-item-row align-top" data-row-index="{{ $rowIndex }}">
                        <td class="px-3 py-3 text-center"><span class="row-number font-bold">{{ $loop->iteration }}</span></td>
                        <td class="relative px-3 py-3">
                            <input type="hidden" name="items[{{ $rowIndex }}][entry_mode]" value="{{ $entryMode }}" class="entry-mode-input">
                            <input type="hidden" name="items[{{ $rowIndex }}][purchase_order_item_id]" value="{{ $oldItem['purchase_order_item_id'] ?? '' }}" class="po-item-id">
                            <input type="hidden" name="items[{{ $rowIndex }}][purchase_order_item_allocation_id]" value="{{ $oldItem['purchase_order_item_allocation_id'] ?? '' }}" class="po-allocation-id">
                            <div class="existing-panel {{ $entryMode === 'temporary' ? 'hidden' : '' }}">
                                <input type="search" class="{{ $inputClass }} material-search-input {{ $isPoReceipt ? 'bg-gray-50 pointer-events-none' : '' }}" autocomplete="off" placeholder="Search product..." {{ $isPoReceipt ? 'readonly' : '' }}>
                                <div class="material-search-results absolute left-3 right-3 z-50 mt-1 hidden max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"></div>
                                @if(!$isPoReceipt)<button type="button" class="enable-temporary-material mt-2 w-full rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">+ Material Not Found</button>@else<span class="enable-temporary-material hidden"></span>@endif
                            </div>
                            <div class="temporary-panel {{ $entryMode === 'temporary' ? '' : 'hidden' }}">
                                <div class="rounded-lg border border-amber-300 bg-amber-50 p-3">
                                    <div class="mb-2 flex justify-between"><span class="text-xs font-bold text-amber-800">PENDING CLASSIFICATION</span><button type="button" class="use-existing-material text-xs font-bold text-blue-700">Search Existing</button></div>
                                    <input type="text" name="items[{{ $rowIndex }}][temporary_material_name]" value="{{ $oldItem['temporary_material_name'] ?? '' }}" class="{{ $inputClass }} temporary-material-name" placeholder="Product name">
                                    <input type="text" name="items[{{ $rowIndex }}][temporary_classification_notes]" value="{{ $oldItem['temporary_classification_notes'] ?? '' }}" class="{{ $inputClass }} mt-2" placeholder="Optional classification note">
                                </div>
                            </div>
                            <select name="items[{{ $rowIndex }}][material_type_id]" class="material-type-select existing-field hidden">
                                <option value="">Select Product</option>
                                @foreach($materialTypes as $materialType)<option value="{{ $materialType->id }}" {{ (string)($oldItem['material_type_id'] ?? '') === (string)$materialType->id ? 'selected' : '' }}>{{ $materialType->material_type_name }}</option>@endforeach
                            </select>
                            <input type="hidden" class="category-display">
                        </td>
                        <td class="px-3 py-3">
                            <div class="existing-field {{ $entryMode === 'temporary' ? 'hidden' : '' }} grid gap-2">
                                <select name="items[{{ $rowIndex }}][material_specification_id]" class="{{ $inputClass }} specification-select" {{ $isPoReceipt ? 'disabled' : '' }}><option value="">Specification</option>@foreach($specifications as $specification)<option value="{{ $specification->id }}" {{ (string)($oldItem['material_specification_id'] ?? '') === (string)$specification->id ? 'selected' : '' }}>{{ $specification->specification_name }}</option>@endforeach</select>
                                @if($isPoReceipt)<input type="hidden" name="items[{{ $rowIndex }}][material_specification_id]" value="{{ $oldItem['material_specification_id'] ?? '' }}">@endif
                                <select name="items[{{ $rowIndex }}][material_grade_id]" class="{{ $inputClass }} grade-select" {{ $isPoReceipt ? 'disabled' : '' }}><option value="">Grade / Rating</option>@foreach($grades as $grade)<option value="{{ $grade->id }}" {{ (string)($oldItem['material_grade_id'] ?? '') === (string)$grade->id ? 'selected' : '' }}>{{ $grade->grade_name }}</option>@endforeach</select>
                                @if($isPoReceipt)<input type="hidden" name="items[{{ $rowIndex }}][material_grade_id]" value="{{ $oldItem['material_grade_id'] ?? '' }}">@endif
                            </div>
                            <div class="temporary-field {{ $entryMode === 'temporary' ? '' : 'hidden' }} grid gap-2"><input type="text" name="items[{{ $rowIndex }}][temporary_specification]" value="{{ $oldItem['temporary_specification'] ?? '' }}" class="{{ $inputClass }}" placeholder="Specification / size"><input type="text" name="items[{{ $rowIndex }}][temporary_grade]" value="{{ $oldItem['temporary_grade'] ?? '' }}" class="{{ $inputClass }}" placeholder="Grade / rating"></div>
                        </td>
                        <td class="px-3 py-3">
                            <select name="items[{{ $rowIndex }}][brand_master_id]" class="{{ $inputClass }} brand-select existing-field {{ $entryMode === 'temporary' ? 'hidden' : '' }}" {{ $isPoReceipt ? 'disabled' : '' }}><option value="">Brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" {{ (string)($oldItem['brand_master_id'] ?? '') === (string)$brand->id ? 'selected' : '' }}>{{ $brand->brand_name }}</option>@endforeach</select>
                            @if($isPoReceipt)<input type="hidden" name="items[{{ $rowIndex }}][brand_master_id]" value="{{ $oldItem['brand_master_id'] ?? '' }}">@endif
                            <input type="text" name="items[{{ $rowIndex }}][temporary_brand]" value="{{ $oldItem['temporary_brand'] ?? '' }}" class="{{ $inputClass }} temporary-field {{ $entryMode === 'temporary' ? '' : 'hidden' }}" placeholder="Brand">
                        </td>
                        @if($isPoReceipt)
                            <td class="px-3 py-3 text-right font-semibold">{{ formatQuantity($oldItem['po_ordered_quantity'] ?? 0) }}</td>
                            <td class="px-3 py-3 text-right">{{ formatQuantity($oldItem['po_previously_accounted'] ?? 0) }}</td>
                            <td class="px-3 py-3 text-right font-bold text-blue-700"><span class="po-pending-quantity">{{ formatQuantity($oldItem['po_pending_quantity'] ?? 0) }}</span></td>
                        @endif
                        <td class="px-3 py-3"><input type="number" step="0.001" min="0.001" name="items[{{ $rowIndex }}][quantity_received]" value="{{ $oldItem['quantity_received'] ?? '' }}" class="{{ $inputClass }} quantity-input text-right" required></td>
                        <td class="px-3 py-3">
                            <input type="hidden" name="items[{{ $rowIndex }}][unit_master_id]" value="{{ $oldItem['unit_master_id'] ?? '' }}" class="unit-id-input">
                            <input type="hidden" class="unit-name-input">
                            <select class="{{ $inputClass }} transaction-unit-select {{ $isPoReceipt ? 'bg-gray-50' : '' }}" {{ $isPoReceipt ? 'disabled' : '' }}>
                                <option value="">Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}" {{ (string)($oldItem['unit_master_id'] ?? '') === (string)$unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>@endforeach
                            </select>
                            <select class="temporary-unit-select hidden"><option value=""></option>@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>@endforeach</select>
                        </td>
                        <td class="px-3 py-3"><input type="number" step="0.001" min="0" name="items[{{ $rowIndex }}][accepted_quantity]" value="{{ $oldItem['accepted_quantity'] ?? '' }}" class="{{ $inputClass }} accepted-input text-right"></td>
                        <td class="px-3 py-3"><input type="number" step="0.001" min="0" name="items[{{ $rowIndex }}][short_quantity]" value="{{ $oldItem['short_quantity'] ?? 0 }}" class="{{ $inputClass }} short-input text-right"></td>
                        <td class="px-3 py-3"><input type="number" step="0.001" min="0" name="items[{{ $rowIndex }}][damaged_quantity]" value="{{ $oldItem['damaged_quantity'] ?? 0 }}" class="{{ $inputClass }} damaged-input text-right"></td>
                        <td class="px-3 py-3"><input type="number" step="0.001" min="0" name="items[{{ $rowIndex }}][rejected_quantity]" value="{{ $oldItem['rejected_quantity'] ?? 0 }}" class="{{ $inputClass }} rejected-input text-right"></td>
                        <td class="px-3 py-3">
                            <input type="text" name="items[{{ $rowIndex }}][purpose_used_for]" value="{{ $oldItem['purpose_used_for'] ?? '' }}" class="{{ $inputClass }}" placeholder="Purpose / used for">
                            <input type="text" name="items[{{ $rowIndex }}][remarks]" value="{{ $oldItem['remarks'] ?? '' }}" class="{{ $inputClass }} mt-2" placeholder="Remarks">
                            <input type="hidden" name="items[{{ $rowIndex }}][rate]" value="{{ $oldItem['rate'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][discount_amount]" value="{{ $oldItem['discount_amount'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][tax_percent]" value="{{ $oldItem['tax_percent'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][tax_amount]" value="{{ $oldItem['tax_amount'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][line_amount]" value="{{ $oldItem['line_amount'] ?? '' }}">
                        </td>
                        <td class="px-3 py-3 text-center"><button type="button" class="remove-item-row rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Remove</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
