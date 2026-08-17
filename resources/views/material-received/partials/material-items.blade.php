{{-- Material item grid --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="flex flex-col gap-3 border-b border-gray-200 bg-[#0F2A52] p-4 text-white md:flex-row md:items-center md:justify-between md:p-5">
        <div>
            <h2 class="text-lg font-bold text-white sm:text-xl">
                Material Items
            </h2>

            <p class="mt-1 text-xs text-blue-100 sm:text-sm">
                Add every material received under this delivery.
            </p>
        </div>

        <button type="button"
                id="add-item-row"
                class="w-full rounded-lg bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700 md:w-auto md:py-2">
            + Add Material Row
        </button>
    </div>

    <div class="overflow-visible lg:overflow-x-auto">

        <table class="block w-full text-sm lg:table lg:min-w-[1800px]">

            <thead class="hidden bg-gray-100 text-xs uppercase tracking-wide text-gray-600 lg:table-header-group">
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
                   class="block space-y-4 p-3 lg:table-row-group lg:space-y-0 lg:p-0 lg:divide-y lg:divide-gray-200">

                @foreach($oldItems as $rowIndex => $oldItem)
                    <tr class="material-item-row block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:table-row lg:rounded-none lg:border-0 lg:shadow-none"
                        data-row-index="{{ $rowIndex }}">

                        <td class="block bg-slate-50 px-3 py-3 lg:table-cell lg:bg-transparent lg:text-center">
                            <div class="flex items-center justify-between lg:block">
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-500 lg:hidden">Material Item</span>
                                <span class="row-number inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 lg:bg-transparent lg:text-sm lg:text-inherit">
                                    {{ $loop->iteration }}
                                </span>
                            </div>
                        </td>

                        <td data-mobile-label="Activity Division" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select name="items[{{ $rowIndex }}][activity_division_id]"
                                    class="{{ $inputClass }} activity-division-select">

                                <option value="">Select Division</option>

                                @foreach($activityDivisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ (string) ($oldItem['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Activity / Material" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select name="items[{{ $rowIndex }}][activity_id]"
                                    class="{{ $inputClass }} activity-select">

                                <option value="">Select Activity</option>

                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}"
                                            data-division="{{ $activity->activity_division_id }}"
                                        {{ (string) ($oldItem['activity_id'] ?? '') === (string) $activity->id ? 'selected' : '' }}>
                                        {{ $activity->activity_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Material Group" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select class="{{ $inputClass }} material-group-select">
                                <option value="">Select Group</option>

                                @foreach($materialGroups as $group)
                                    <option value="{{ $group }}">
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Material Type" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select name="items[{{ $rowIndex }}][material_type_id]"
                                    class="{{ $inputClass }} material-type-select"
                                    required>

                                <option value="">Select Material Type</option>

                                @foreach($materialTypes as $materialType)
                                    <option value="{{ $materialType->id }}"
                                            data-group="{{ $materialType->material_group }}"
                                            data-unit-id="{{ $materialType->unit_master_id }}"
                                            data-unit-name="{{ $materialType->unit?->unit_name }}"
                                        {{ (string) ($oldItem['material_type_id'] ?? '') === (string) $materialType->id ? 'selected' : '' }}>
                                        {{ $materialType->material_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Brand" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select name="items[{{ $rowIndex }}][brand_master_id]"
                                    class="{{ $inputClass }} brand-select">

                                <option value="">Select Brand</option>

                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                            data-material-type="{{ $brand->material_type_id }}"
                                        {{ (string) ($oldItem['brand_master_id'] ?? '') === (string) $brand->id ? 'selected' : '' }}>
                                        {{ $brand->brand_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Specification" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select name="items[{{ $rowIndex }}][material_specification_id]"
                                    class="{{ $inputClass }} specification-select">

                                <option value="">Select Specification</option>

                                @foreach($specifications as $specification)
                                    <option value="{{ $specification->id }}"
                                            data-material-type="{{ $specification->material_type_id }}"
                                        {{ (string) ($oldItem['material_specification_id'] ?? '') === (string) $specification->id ? 'selected' : '' }}>
                                        {{ $specification->specification_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Grade / Rating" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <select name="items[{{ $rowIndex }}][material_grade_id]"
                                    class="{{ $inputClass }} grade-select">

                                <option value="">Select Grade / Rating</option>

                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}"
                                            data-material-type="{{ $grade->material_type_id }}"
                                        {{ (string) ($oldItem['material_grade_id'] ?? '') === (string) $grade->id ? 'selected' : '' }}>
                                        {{ $grade->grade_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td data-mobile-label="Quantity" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <input type="number"
                                   step="0.001"
                                   min="0.001"
                                   name="items[{{ $rowIndex }}][quantity_received]"
                                   value="{{ $oldItem['quantity_received'] ?? '' }}"
                                   class="{{ $inputClass }}"
                                   required>
                        </td>

                        <td data-mobile-label="Unit" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <input type="hidden"
                                   name="items[{{ $rowIndex }}][unit_master_id]"
                                   value="{{ $oldItem['unit_master_id'] ?? '' }}"
                                   class="unit-id-input">

                            <input type="text"
                                   class="{{ $inputClass }} unit-name-input bg-gray-100"
                                   readonly
                                   placeholder="Auto">
                        </td>

                        <td data-mobile-label="Remarks" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:before:hidden">
                            <input type="text"
                                   name="items[{{ $rowIndex }}][remarks]"
                                   value="{{ $oldItem['remarks'] ?? '' }}"
                                   class="{{ $inputClass }}"
                                   placeholder="Optional">
                        </td>

                        <td data-mobile-label="Action" class="block px-3 py-3 before:mb-1 before:block before:text-[10px] before:font-bold before:uppercase before:tracking-wide before:text-gray-500 before:content-[attr(data-mobile-label)] lg:table-cell lg:text-center lg:before:hidden">
                            <button type="button"
                                    class="remove-item-row w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 lg:w-auto lg:border-0 lg:bg-red-600 lg:text-white lg:hover:bg-red-700">
                                Remove
                            </button>
                        </td>

                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 p-4 text-xs text-gray-500 sm:p-5 sm:text-sm">
        Brand, Specification, Grade/Rating and Unit are filtered automatically from the selected Material Type.
    </div>
</div>
