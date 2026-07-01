<x-rds.section title="Basic Information">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-rds.input name="contractor_code" label="Contractor Code" value="{{ old('contractor_code', $contractor->contractor_code ?? '') }}" placeholder="Auto if blank" />
        <x-rds.input name="contractor_name" label="Contractor Name" value="{{ old('contractor_name', $contractor->contractor_name ?? '') }}" required />
        <x-rds.input name="company_name" label="Company Name" value="{{ old('company_name', $contractor->company_name ?? '') }}" />
        <x-rds.input name="mobile" label="Mobile" value="{{ old('mobile', $contractor->mobile ?? '') }}" required />
        <x-rds.input name="alternate_mobile" label="Alternate Mobile" value="{{ old('alternate_mobile', $contractor->alternate_mobile ?? '') }}" />
        <x-rds.input type="email" name="email" label="Email" value="{{ old('email', $contractor->email ?? '') }}" />
    </div>
</x-rds.section>

<x-rds.section title="Work Classification" class="mt-6">
    @php
        $selectedDivisions = old(
            'division_ids',
            isset($contractor) ? $contractor->divisions->pluck('id')->toArray() : []
        );

        $selectedServices = old(
            'service_category_ids',
            isset($contractor) ? $contractor->serviceCategories->pluck('id')->toArray() : []
        );

        $serviceOptions = $serviceCategories->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => ($service->division?->name ? $service->division->name . ' - ' : '') . $service->name,
            ];
        });
    @endphp

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <x-rds.multiselect
            name="division_ids"
            label="Work Divisions"
            :items="$activityDivisions"
            :selected="$selectedDivisions"
            value-key="id"
            label-key="name"
            placeholder="Select work divisions"
        />

        <x-rds.multiselect
            name="service_category_ids"
            label="Service Categories Optional"
            :items="$serviceOptions"
            :selected="$selectedServices"
            value-key="id"
            label-key="name"
            placeholder="Select service categories"
        />
    </div>
</x-rds.section>

<x-rds.section title="Address" class="mt-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-rds.input name="city" label="City / Location" value="{{ old('city', $contractor->city ?? '') }}" />
        <x-rds.input name="district" label="District" value="{{ old('district', $contractor->district ?? '') }}" />
        <x-rds.input name="state" label="State" value="{{ old('state', $contractor->state ?? 'Telangana') }}" />
        <x-rds.input name="pincode" label="PIN Code" value="{{ old('pincode', $contractor->pincode ?? '') }}" />

        <div class="md:col-span-2">
            <x-rds.textarea name="address" label="Full Address" rows="3" value="{{ old('address', $contractor->address ?? '') }}" />
        </div>
    </div>
</x-rds.section>

<x-rds.section title="Compliance & Experience" class="mt-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-rds.input name="gst_number" label="GST Number" value="{{ old('gst_number', $contractor->gst_number ?? '') }}" />
        <x-rds.input name="pan_number" label="PAN Number" value="{{ old('pan_number', $contractor->pan_number ?? '') }}" />
        <x-rds.input name="aadhaar_number" label="Aadhaar / ID Proof Number" value="{{ old('aadhaar_number', $contractor->aadhaar_number ?? '') }}" />
        <x-rds.input name="license_number" label="License Number" value="{{ old('license_number', $contractor->license_number ?? '') }}" />
        <x-rds.input type="number" name="experience_years" label="Experience Years" value="{{ old('experience_years', $contractor->experience_years ?? '') }}" />
        <x-rds.input type="number" name="rating" label="Rating 1 to 5" value="{{ old('rating', $contractor->rating ?? '') }}" />

        <x-rds.select name="status" label="Status" required>
            <option value="Active" @selected(old('status', $contractor->status ?? 'Active') === 'Active')>Active</option>
            <option value="Inactive" @selected(old('status', $contractor->status ?? 'Active') === 'Inactive')>Inactive</option>
        </x-rds.select>

        <div class="flex items-center pt-6">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_preferred" value="1" class="rounded border-gray-300"
                       @checked(old('is_preferred', $contractor->is_preferred ?? false))>
                <span class="text-sm font-semibold text-gray-700">Preferred Contractor</span>
            </label>
        </div>

        <div class="md:col-span-2">
            <x-rds.textarea name="remarks" label="Remarks" rows="3" value="{{ old('remarks', $contractor->remarks ?? '') }}" />
        </div>
    </div>
</x-rds.section>