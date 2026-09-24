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
            <button type="button" id="add-item-row" class="hidden">+ Add Row</button>
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

    @if(!$isPoReceipt)
    <div id="direct-entry-card" class="mx-4 my-5 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:mx-5 sm:p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><h3 class="text-base font-bold text-[#10212F]">Add Material to Receipt</h3><span id="direct-entry-status" class="text-xs text-slate-500">Choose a product and enter its receipt details</span></div>
        <div id="direct-entry-host"></div>
        <div class="mt-4 flex flex-wrap gap-3"><button type="button" id="commit-direct-item" class="rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">+ Add to Receipt</button><button type="button" id="cancel-direct-edit" class="hidden rounded-lg border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-700">Cancel Edit</button></div>
    </div>
    <div id="direct-summary-card" class="mx-4 mb-5 overflow-hidden rounded-xl border border-slate-200 sm:mx-5">
        <div class="flex items-center justify-between bg-slate-100 px-4 py-3"><h3 class="font-bold text-[#10212F]">Material Items <span id="direct-item-count" class="text-sm font-normal text-slate-500">(0)</span></h3><span class="text-xs text-slate-500">Edit or remove an item below</span></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-sm"><thead class="bg-white text-left text-xs uppercase text-slate-500"><tr><th class="px-3 py-3">#</th><th class="px-3 py-3">Product / Specification / Brand</th><th class="px-3 py-3 text-right">Received</th><th class="px-3 py-3 text-right">Accepted</th><th class="px-3 py-3">Remarks</th><th class="px-3 py-3">Action</th></tr></thead><tbody id="direct-summary-body" class="divide-y divide-slate-100"></tbody></table></div>
        <p id="direct-empty-message" class="px-4 py-5 text-sm text-slate-500">No materials added yet. Complete the form above and click Add to Receipt.</p>
    </div>
    @endif
    <div class="{{ $isPoReceipt ? 'po-material-scroll overflow-auto' : 'overflow-x-auto direct-original-table' }}">
        <table class="min-w-[1850px] w-full text-sm">
            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600 {{ $isPoReceipt ? 'sticky top-0 z-10' : '' }}">
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
                                @if($isPoReceipt)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-semibold text-slate-800">{{ $materialTypes->firstWhere('id', $oldItem['material_type_id'] ?? null)?->material_type_name ?? 'PO Product' }}</div>
                                    <input type="search" class="material-search-input hidden" readonly tabindex="-1" aria-hidden="true">
                                @else
                                    <input type="search" class="{{ $inputClass }} material-search-input" autocomplete="off" placeholder="Search product...">
                                @endif
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
                            @if($isPoReceipt)
                                <input type="hidden" name="items[{{ $rowIndex }}][material_type_id]" value="{{ $oldItem['material_type_id'] ?? '' }}" class="material-type-select existing-field">
                            @else
                                <select name="items[{{ $rowIndex }}][material_type_id]" class="material-type-select existing-field hidden">
                                    <option value="">Select Product</option>
                                    @foreach($materialTypes as $materialType)<option value="{{ $materialType->id }}" {{ (string)($oldItem['material_type_id'] ?? '') === (string)$materialType->id ? 'selected' : '' }}>{{ $materialType->material_type_name }}</option>@endforeach
                                </select>
                            @endif
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
                            <input type="text" name="items[{{ $rowIndex }}][purpose_used_for]" value="{{ $oldItem['purpose_used_for'] ?? '' }}" class="{{ $inputClass }} hidden" placeholder="Purpose / used for">
                            <input type="text" name="items[{{ $rowIndex }}][remarks]" value="{{ $oldItem['remarks'] ?? '' }}" class="{{ $inputClass }}" placeholder="Remarks (optional)">
                            <input type="hidden" name="items[{{ $rowIndex }}][rate]" value="{{ $oldItem['rate'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][discount_amount]" value="{{ $oldItem['discount_amount'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][tax_percent]" value="{{ $oldItem['tax_percent'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][tax_amount]" value="{{ $oldItem['tax_amount'] ?? '' }}"><input type="hidden" name="items[{{ $rowIndex }}][line_amount]" value="{{ $oldItem['line_amount'] ?? '' }}">
                        </td>
                        <td class="px-3 py-3 text-center"><button type="button" class="remove-item-row rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Remove</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(!$isPoReceipt)
<style>
/* Compact desktop entry: one product-details row, one quantity row, then actions. */
.direct-original-table { display:none!important; }
#direct-entry-card { margin:12px 16px 14px; padding:12px 14px; }
#direct-entry-card>div:first-child { margin-bottom:10px; }
#direct-entry-card .material-item-row { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:9px 12px; align-items:start; }
#direct-entry-card .material-item-row>td { display:block!important; padding:0!important; min-width:0!important; }
#direct-entry-card .material-item-row>td:first-child,
#direct-entry-card .material-item-row>td:last-child { display:none!important; }
#direct-entry-card .material-item-row>td:nth-child(2) { grid-column:span 5; }
#direct-entry-card .material-item-row>td:nth-child(3) { grid-column:span 4; }
#direct-entry-card .material-item-row>td:nth-child(4) { grid-column:span 3; }
#direct-entry-card .material-item-row>td:nth-child(n+5):nth-child(-n+10) { grid-column:span 2; }
#direct-entry-card .material-item-row>td:nth-child(11) { grid-column:span 12; display:grid!important; grid-template-columns:1fr; gap:0; }
#direct-entry-card .material-item-row>td:nth-child(11) input:not(.hidden) { margin:0!important; }
#direct-entry-card .material-item-row>td:nth-child(11) input.hidden { display:none!important; }
#direct-entry-card .material-item-row>td:nth-child(2):before { content:'Product'; }
#direct-entry-card .material-item-row>td:nth-child(3):before { content:'Specification / Grade'; }
#direct-entry-card .material-item-row>td:nth-child(4):before { content:'Brand'; }
#direct-entry-card .material-item-row>td:nth-child(5):before { content:'Receive Now'; }
#direct-entry-card .material-item-row>td:nth-child(6):before { content:'Unit'; }
#direct-entry-card .material-item-row>td:nth-child(7):before { content:'Accepted'; }
#direct-entry-card .material-item-row>td:nth-child(8):before { content:'Short'; }
#direct-entry-card .material-item-row>td:nth-child(9):before { content:'Damaged'; }
#direct-entry-card .material-item-row>td:nth-child(10):before { content:'Rejected'; }
#direct-entry-card .material-item-row>td:nth-child(11):before { content:'Remarks'; grid-column:1/-1; }
#direct-entry-card .material-item-row>td:nth-child(n+2):nth-child(-n+11):before { display:block; font-size:11px; font-weight:600; color:#334155; margin-bottom:4px; }
#direct-entry-card .material-item-row input:not([type=hidden]),
#direct-entry-card .material-item-row select:not(.hidden) { min-height:36px; padding:6px 9px; font-size:13px; }
#direct-entry-card .material-item-row .material-type-select,
#direct-entry-card .material-item-row .category-display,
#direct-entry-card .material-item-row .temporary-unit-select { display:none!important; }
#direct-entry-card .material-item-row .existing-field.grid { grid-template-columns:1fr 1fr; gap:8px; }
/* Keep Product, Specification / Grade and Brand on the same top line. */
#direct-entry-card .material-item-row>td:nth-child(2),
#direct-entry-card .material-item-row>td:nth-child(3),
#direct-entry-card .material-item-row>td:nth-child(4) { align-self:start; }
#direct-entry-card .material-item-row .enable-temporary-material { width:auto; margin-top:5px; padding:4px 9px; font-size:11px; }
#direct-entry-card .material-item-row .temporary-panel .rounded-lg { padding:7px; }
#direct-entry-card .material-item-row .temporary-panel input.mt-2 { margin-top:5px; }
#direct-entry-card #commit-direct-item,
#direct-entry-card #cancel-direct-edit { padding:8px 14px; font-size:13px; }
#direct-entry-card>div:last-child { margin-top:10px; }
@media(max-width:1100px) {
 #direct-entry-card .material-item-row>td:nth-child(2) { grid-column:span 12; }
 #direct-entry-card .material-item-row>td:nth-child(3),
 #direct-entry-card .material-item-row>td:nth-child(4) { grid-column:span 6; }
 #direct-entry-card .material-item-row>td:nth-child(n+5):nth-child(-n+10) { grid-column:span 4; }
}
@media(max-width:640px) {
 #direct-entry-card .material-item-row>td:nth-child(n) { grid-column:span 12; }
 #direct-entry-card .material-item-row>td:nth-child(n+5):nth-child(-n+10) { grid-column:span 6; }
 #direct-entry-card .material-item-row>td:nth-child(11) { grid-template-columns:1fr; }
}
</style>
@endif

@if($isPoReceipt)
<style>
/* PO items: independent two-axis scrolling, without affecting Direct Receipts. */
.po-material-scroll {
    height: 450px;
    max-height: 60vh;
    min-height: 220px;
    overflow-x: scroll;
    overflow-y: scroll;
    scrollbar-gutter: stable;
    overscroll-behavior: contain;
}
.po-material-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f3f4f6;
}
/* Chromium/Edge scrollbar styling; native scrollbar behavior remains intact. */
.po-material-scroll::-webkit-scrollbar { width: 12px; height: 12px; }
.po-material-scroll::-webkit-scrollbar-track { background: #e2e8f0; }
.po-material-scroll::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
}
.po-material-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>
@endif
