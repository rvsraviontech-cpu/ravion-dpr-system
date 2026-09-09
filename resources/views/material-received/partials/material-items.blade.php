{{-- Simple Material Received: what physically arrived --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-gray-200 bg-[#10212F] p-4 text-white md:flex-row md:items-center md:justify-between md:p-5">
        <div>
            <h2 class="text-lg font-bold sm:text-xl">Material Items</h2>
            <p class="mt-1 text-xs text-slate-200 sm:text-sm">Search the product, enter quantity and optional receipt details. Nothing else is required.</p>
        </div>
        <button type="button" id="add-item-row" class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700 md:w-auto md:py-2">+ Add Row</button>
    </div>

    <div class="border-b border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800">
        Product not found? Use <strong>+ Material Not Found</strong>. The receipt can continue and the product will go to Pending Classification.
    </div>

    <div class="overflow-visible lg:overflow-x-auto">
        <table class="block w-full text-sm lg:table lg:min-w-[1900px]">
            <thead class="hidden bg-gray-100 text-xs uppercase tracking-wide text-gray-600 lg:table-header-group">
                <tr>
                    <th class="w-14 px-3 py-3 text-center">#</th>
                    <th class="min-w-[300px] px-3 py-3 text-left">Search Material</th>
                    <th class="min-w-48 px-3 py-3 text-left">Category</th>
                    <th class="min-w-56 px-3 py-3 text-left">Product</th>
                    <th class="min-w-[300px] px-3 py-3 text-left">Specification / Grade</th>
                    <th class="min-w-44 px-3 py-3 text-left">Brand</th>
                    <th class="min-w-32 px-3 py-3 text-left">Qty</th>
                    <th class="min-w-32 px-3 py-3 text-left">Unit</th>
                    <th class="min-w-52 px-3 py-3 text-left">Purpose / Used For</th>
                    <th class="min-w-52 px-3 py-3 text-left">Remarks</th>
                    <th class="w-24 px-3 py-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody id="material-items-body" class="block space-y-4 p-3 lg:table-row-group lg:space-y-0 lg:p-0 lg:divide-y lg:divide-gray-200">
                @foreach($oldItems as $rowIndex => $oldItem)
                    @php $entryMode = $oldItem['entry_mode'] ?? 'existing'; @endphp
                    <tr class="material-item-row block overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm lg:table-row lg:rounded-none lg:border-0 lg:shadow-none" data-row-index="{{ $rowIndex }}">
                        <td class="block bg-slate-50 px-3 py-3 lg:table-cell lg:bg-transparent lg:text-center">
                            <span class="row-number font-bold">{{ $loop->iteration }}</span>
                        </td>

                        <td data-mobile-label="Search Material" class="relative block px-3 py-3 lg:table-cell">
                            <input type="hidden" name="items[{{ $rowIndex }}][entry_mode]" value="{{ $entryMode }}" class="entry-mode-input">
                            <div class="existing-panel {{ $entryMode === 'temporary' ? 'hidden' : '' }}">
                                <div class="relative">
                                    <input type="search" class="{{ $inputClass }} material-search-input" autocomplete="off" placeholder="Search cement, diesel, pipe, cable...">
                                    <div class="material-search-results absolute left-0 right-0 z-50 mt-1 hidden max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"></div>
                                </div>
                                <button type="button" class="enable-temporary-material mt-2 w-full rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100">+ Material Not Found</button>
                            </div>
                            <div class="temporary-panel {{ $entryMode === 'temporary' ? '' : 'hidden' }}">
                                <div class="rounded-lg border border-amber-300 bg-amber-50 p-3">
                                    <div class="mb-2 flex justify-between gap-2">
                                        <span class="text-xs font-bold text-amber-800">PENDING CLASSIFICATION</span>
                                        <button type="button" class="use-existing-material text-xs font-bold text-blue-700">Search Existing</button>
                                    </div>
                                    <input type="text" name="items[{{ $rowIndex }}][temporary_material_name]" value="{{ $oldItem['temporary_material_name'] ?? '' }}" class="{{ $inputClass }} temporary-material-name" placeholder="Product name">
                                    <input type="text" name="items[{{ $rowIndex }}][temporary_classification_notes]" value="{{ $oldItem['temporary_classification_notes'] ?? '' }}" class="{{ $inputClass }} mt-2" placeholder="Optional classification note">
                                </div>
                            </div>
                        </td>

                        <td data-mobile-label="Category" class="block px-3 py-3 lg:table-cell">
                            <input type="text" class="{{ $inputClass }} category-display bg-gray-50" readonly placeholder="{{ $entryMode === 'temporary' ? 'Pending' : 'Auto' }}">
                        </td>

                        <td data-mobile-label="Product" class="block px-3 py-3 lg:table-cell">
                            <select name="items[{{ $rowIndex }}][material_type_id]" class="{{ $inputClass }} material-type-select existing-field {{ $entryMode === 'temporary' ? 'hidden' : '' }}">
                                <option value="">Select Product</option>
                                @foreach($materialTypes as $materialType)
                                    <option value="{{ $materialType->id }}" {{ (string) ($oldItem['material_type_id'] ?? '') === (string) $materialType->id ? 'selected' : '' }}>{{ $materialType->material_type_name }}</option>
                                @endforeach
                            </select>
                            <div class="temporary-field {{ $entryMode === 'temporary' ? '' : 'hidden' }} rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Will be mapped later</div>
                        </td>

                        <td data-mobile-label="Specification / Grade" class="block px-3 py-3 lg:table-cell">
                            <div class="existing-field {{ $entryMode === 'temporary' ? 'hidden' : '' }} grid grid-cols-1 gap-2">
                                <select name="items[{{ $rowIndex }}][material_specification_id]" class="{{ $inputClass }} specification-select">
<option value="">Specification</option>
@foreach($specifications as $specification)<option value="{{ $specification->id }}" {{ (string) ($oldItem['material_specification_id'] ?? '') === (string) $specification->id ? 'selected' : '' }}>{{ $specification->specification_name }}</option>@endforeach
</select>
                                <select name="items[{{ $rowIndex }}][material_grade_id]" class="{{ $inputClass }} grade-select">
<option value="">Grade / Rating</option>
@foreach($grades as $grade)<option value="{{ $grade->id }}" {{ (string) ($oldItem['material_grade_id'] ?? '') === (string) $grade->id ? 'selected' : '' }}>{{ $grade->grade_name }}</option>@endforeach
</select>
                            </div>
                            <div class="temporary-field {{ $entryMode === 'temporary' ? '' : 'hidden' }} grid grid-cols-1 gap-2">
                                <input type="text" name="items[{{ $rowIndex }}][temporary_specification]" value="{{ $oldItem['temporary_specification'] ?? '' }}" class="{{ $inputClass }}" placeholder="Specification / size">
                                <input type="text" name="items[{{ $rowIndex }}][temporary_grade]" value="{{ $oldItem['temporary_grade'] ?? '' }}" class="{{ $inputClass }}" placeholder="Grade / rating">
                            </div>
                        </td>

                        <td data-mobile-label="Brand" class="block px-3 py-3 lg:table-cell">
                            <select name="items[{{ $rowIndex }}][brand_master_id]" class="{{ $inputClass }} brand-select existing-field {{ $entryMode === 'temporary' ? 'hidden' : '' }}">
<option value="">Brand</option>
@foreach($brands as $brand)<option value="{{ $brand->id }}" {{ (string) ($oldItem['brand_master_id'] ?? '') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->brand_name }}</option>@endforeach
</select>
                            <input type="text" name="items[{{ $rowIndex }}][temporary_brand]" value="{{ $oldItem['temporary_brand'] ?? '' }}" class="{{ $inputClass }} temporary-field {{ $entryMode === 'temporary' ? '' : 'hidden' }}" placeholder="Brand">
                        </td>

                        <td data-mobile-label="Qty" class="block px-3 py-3 lg:table-cell">
                            <input type="number" step="0.001" min="0.001" name="items[{{ $rowIndex }}][quantity_received]" value="{{ $oldItem['quantity_received'] ?? '' }}" class="{{ $inputClass }} quantity-input" required>
                        </td>

                        <td data-mobile-label="Unit" class="block px-3 py-3 lg:table-cell">
                            <input type="hidden" name="items[{{ $rowIndex }}][unit_master_id]" value="{{ $oldItem['unit_master_id'] ?? '' }}" class="unit-id-input">
                            <input type="text" class="{{ $inputClass }} unit-name-input existing-field {{ $entryMode === 'temporary' ? 'hidden' : '' }} bg-gray-50" readonly placeholder="Auto">
                            <select class="{{ $inputClass }} temporary-unit-select temporary-field {{ $entryMode === 'temporary' ? '' : 'hidden' }}">
                                <option value="">Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ (string) ($oldItem['unit_master_id'] ?? '') === (string) $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Purpose / Used For" class="block px-3 py-3 lg:table-cell">
                            <input type="text" name="items[{{ $rowIndex }}][purpose_used_for]" value="{{ $oldItem['purpose_used_for'] ?? '' }}" class="{{ $inputClass }}" placeholder="e.g. Lift Operating Machine">
                        </td>

                        <td data-mobile-label="Remarks" class="block px-3 py-3 lg:table-cell">
                            <input type="text" name="items[{{ $rowIndex }}][remarks]" value="{{ $oldItem['remarks'] ?? '' }}" class="{{ $inputClass }}" placeholder="Optional">
                        </td>

                        <td data-mobile-label="Action" class="block px-3 py-3 lg:table-cell lg:text-center">
                            <button type="button" class="remove-item-row rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
