{{-- Project and delivery header --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

    <div class="mb-4 rounded-xl bg-[#0F2A52] px-4 py-3 text-white sm:mb-5">
        <h2 class="text-lg font-bold sm:text-xl">Delivery Information</h2>
        <p class="mt-1 text-xs text-blue-100">Project, location and supplier details for this receipt.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

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
                        {{ (string) old('project_id') === (string) $project->id ? 'selected' : '' }}>
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
                        {{ (string) old('project_block_id') === (string) $block->id ? 'selected' : '' }}>
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
                        {{ (string) old('project_floor_id') === (string) $floor->id ? 'selected' : '' }}>
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
                        {{ (string) old('project_unit_id') === (string) $projectUnit->id ? 'selected' : '' }}>
                        {{ $projectUnit->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $labelClass }}">Storage Location</label>

            <input type="text"
                   name="storage_location"
                   value="{{ old('storage_location') }}"
                   class="{{ $inputClass }}"
                   placeholder="Example: Site Store, Ground Floor Yard">
        </div>

        <div>
            <label class="{{ $labelClass }}">
                Received Date <span class="text-red-500">*</span>
            </label>

            <input type="date"
                   name="received_date"
                   value="{{ old('received_date', now()->format('Y-m-d')) }}"
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
                        {{ (string) old('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->vendor_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $labelClass }}">Contractor Supply</label>

            <label class="mt-2 flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-3">
                <input type="checkbox"
                       name="supplied_by_contractor"
                       id="supplied_by_contractor"
                       value="1"
                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                       {{ old('supplied_by_contractor') ? 'checked' : '' }}>

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
                        {{ (string) old('contractor_id') === (string) $contractor->id ? 'selected' : '' }}>
                        {{ $contractor->contractor_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $labelClass }}">Vehicle Number</label>

            <input type="text"
                   name="vehicle_number"
                   value="{{ old('vehicle_number') }}"
                   class="{{ $inputClass }}"
                   placeholder="Example: TS09AB1234">
        </div>

        <div>
            <label class="{{ $labelClass }}">Driver Name</label>

            <input type="text"
                   name="driver_name"
                   value="{{ old('driver_name') }}"
                   class="{{ $inputClass }}">
        </div>

        <div>
            <label class="{{ $labelClass }}">Challan Number</label>

            <input type="text"
                   name="challan_number"
                   value="{{ old('challan_number') }}"
                   class="{{ $inputClass }}">
        </div>

        <div>
            <label class="{{ $labelClass }}">Bill Number</label>

            <input type="text"
                   name="bill_number"
                   value="{{ old('bill_number') }}"
                   class="{{ $inputClass }}">
        </div>

        <div class="md:col-span-2 xl:col-span-4">
            <label class="{{ $labelClass }}">Delivery Remarks</label>

            <textarea name="remarks"
                      rows="3"
                      class="{{ $inputClass }}"
                      placeholder="General notes for this delivery">{{ old('remarks') }}</textarea>
        </div>

    </div>
</div>
