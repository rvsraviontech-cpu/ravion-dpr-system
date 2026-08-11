{{-- Material item grid --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="flex flex-col gap-3 border-b border-gray-200 p-5 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Material Items
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Add every material received under this delivery.
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

                @foreach($oldItems as $rowIndex => $oldItem)
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
                                        {{ (string) ($oldItem['activity_division_id'] ?? '') === (string) $division->id ? 'selected' : '' }}>
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
                                        {{ (string) ($oldItem['activity_id'] ?? '') === (string) $activity->id ? 'selected' : '' }}>
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
                                            data-unit-name="{{ $materialType->unit?->unit_name }}"
                                        {{ (string) ($oldItem['material_type_id'] ?? '') === (string) $materialType->id ? 'selected' : '' }}>
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
                                        {{ (string) ($oldItem['brand_master_id'] ?? '') === (string) $brand->id ? 'selected' : '' }}>
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
                                        {{ (string) ($oldItem['material_specification_id'] ?? '') === (string) $specification->id ? 'selected' : '' }}>
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
                                        {{ (string) ($oldItem['material_grade_id'] ?? '') === (string) $grade->id ? 'selected' : '' }}>
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
                                   value="{{ $oldItem['quantity_received'] ?? '' }}"
                                   class="{{ $inputClass }}"
                                   required>
                        </td>

                        <td class="px-3 py-3">
                            <input type="hidden"
                                   name="items[{{ $rowIndex }}][unit_master_id]"
                                   value="{{ $oldItem['unit_master_id'] ?? '' }}"
                                   class="unit-id-input">

                            <input type="text"
                                   class="{{ $inputClass }} unit-name-input bg-gray-100"
                                   readonly
                                   placeholder="Auto">
                        </td>

                        <td class="px-3 py-3">
                            <input type="text"
                                   name="items[{{ $rowIndex }}][remarks]"
                                   value="{{ $oldItem['remarks'] ?? '' }}"
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
