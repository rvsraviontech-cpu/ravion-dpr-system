<div class="space-y-5">

    {{-- PROJECT INFORMATION --}}
    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-3">
            Project Information
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="{{ $label }}">Project Code *</label>
                <input type="text"
       name="project_code"
       class="{{ $input }} bg-gray-100 cursor-not-allowed"
       value="{{ old('project_code', optional($project)->project_code ?? $suggestedProjectCode ?? '') }}"
       readonly
       required>
            </div>

            <div class="md:col-span-2">
                <label class="{{ $label }}">Project Name *</label>
                <input type="text"
                       name="project_name"
                       class="{{ $input }}"
                       value="{{ old('project_name', optional($project)->project_name) }}"
                       required>
            </div>

            <div>
                <label class="{{ $label }}">Status *</label>
                <select name="status" class="{{ $input }}" required>
                    @foreach($projectStatuses as $status)
                        <option value="{{ $status }}"
                            {{ old('status', optional($project)->status ?? 'Active') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $label }}">Project Type</label>
                <select name="project_type" class="{{ $input }}">
                    <option value="">Select Project Type</option>
                    @foreach($projectTypes as $type)
                        <option value="{{ $type }}"
                            {{ old('project_type', optional($project)->project_type) == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $label }}">Structure Type</label>
                <select name="structure_type" class="{{ $input }}">
                    <option value="">Select Structure Type</option>
                    @foreach($structureTypes as $type)
                        <option value="{{ $type }}"
                            {{ old('structure_type', optional($project)->structure_type) == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $label }}">Start Date</label>
                <input type="date"
                       name="start_date"
                       class="{{ $input }}"
                       value="{{ old('start_date', optional(optional($project)->start_date)->format('Y-m-d')) }}">
            </div>

            <div>
                <label class="{{ $label }}">Target Completion</label>
                <input type="date"
                       name="target_completion_date"
                       class="{{ $input }}"
                       value="{{ old('target_completion_date', optional(optional($project)->target_completion_date)->format('Y-m-d')) }}">
            </div>

        </div>
    </div>

    {{-- CLIENT DETAILS --}}
    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-3">
            Client Details
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="{{ $label }}">Client Name</label>
                <input type="text"
                       name="client_name"
                       class="{{ $input }}"
                       value="{{ old('client_name', optional($project)->client_name) }}">
            </div>

            <div>
                <label class="{{ $label }}">Client Mobile</label>
                <input type="text"
                       name="client_mobile"
                       class="{{ $input }}"
                       value="{{ old('client_mobile', optional($project)->client_mobile) }}">
            </div>

            <div>
                <label class="{{ $label }}">Client Email</label>
                <input type="email"
                       name="client_email"
                       class="{{ $input }}"
                       value="{{ old('client_email', optional($project)->client_email) }}">
            </div>

            <div>
                <label class="{{ $label }}">Client Address</label>
                <input type="text"
                       name="client_address"
                       class="{{ $input }}"
                       value="{{ old('client_address', optional($project)->client_address) }}">
            </div>

        </div>
    </div>

    {{-- PROJECT LOCATION --}}
    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-3">
            Project Location
        </h2>

        <x-map-picker
            address="location"
            latitude="latitude"
            longitude="longitude"
            maplink="google_map_link"
            :address-value="optional($project)->location"
            :latitude-value="optional($project)->latitude"
            :longitude-value="optional($project)->longitude"
            :maplink-value="optional($project)->google_map_link"
        />
    </div>

</div>