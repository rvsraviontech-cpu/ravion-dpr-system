@extends('layouts.app')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
            <p class="mt-1 text-sm text-gray-500">
                Update employee details, role, department, designation and optional project access.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('users.show', $user->id) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                View User
            </a>

            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Back to Users
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold">Please correct the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('users.update', $user->id) }}"
          class="space-y-5">

        @csrf

        {{-- Employee Information --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Employee Information
                </h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Update employee and contact details.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Employee Code
                    </label>

                    <input type="text"
                           value="{{ $user->employee_code }}"
                           readonly
                           class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-semibold text-gray-600">

                    <p class="mt-1 text-xs text-gray-400">
                        Permanent user reference.
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Full Name <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           autofocus
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Mobile Number
                    </label>

                    <input type="text"
                           name="mobile"
                           value="{{ old('mobile', $user->mobile) }}"
                           placeholder="+91 98765 43210"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>

                    <input type="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Department
                    </label>

                    <select name="department_id"
                            id="department_id"
                            onchange="filterDesignations(true)"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                        <option value="">Select Department</option>

                        @foreach($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ (string) old('department_id', $user->department_id) === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Designation
                    </label>

                    <select name="employee_designation_id"
                            id="employee_designation_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                        <option value="">Select Department First</option>
                    </select>

                    <p class="mt-1 text-xs text-gray-400">
                        Designations are filtered by the selected department.
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                        Joining Date
                    </label>

                    <input type="date"
                           name="joining_date"
                           value="{{ old('joining_date', $user->joining_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                </div>

            </div>
        </div>

        {{-- ERP Access --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">ERP Access</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Role is required. Project assignment is optional and can also be managed from the Projects module.
                </p>
            </div>

            <div class="space-y-5 p-5">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                            ERP Role <span class="text-red-500">*</span>
                        </label>

                        <select name="role_id"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                            <option value="">Select Role</option>

                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ (string) old('role_id', $user->role_id) === (string) $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Project Access
                        </label>

                        <select name="project_access_scope"
                                id="project_access_scope"
                                onchange="toggleProjectSelection()"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">
                            <option value="selected"
                                {{ old('project_access_scope', $user->project_access_scope ?? 'selected') === 'selected' ? 'selected' : '' }}>
                                Selected Projects
                            </option>

                            <option value="all"
                                {{ old('project_access_scope', $user->project_access_scope) === 'all' ? 'selected' : '' }}>
                                All Projects
                            </option>
                        </select>

                        <p class="mt-1 text-xs text-gray-400">
                            Optional. Leave as Selected Projects with no selections when no project-level access is required.
                        </p>
                    </div>
                </div>

                @php
                    $selectedProjectIds = array_map(
                        'intval',
                        old('project_ids', $user->projects->pluck('id')->all())
                    );
                @endphp

                <div id="project-selection-section">

                    <div class="mb-2 flex items-center justify-between gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                Assigned Projects
                            </label>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Optional — assign here or later from the Projects module.
                            </p>
                        </div>

                        <button type="button"
                                onclick="toggleAllProjects()"
                                class="shrink-0 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                            Select / Clear All
                        </button>
                    </div>

                    <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-200">

                        @forelse($projects as $project)

                            <label class="flex cursor-pointer items-start gap-3 border-b border-gray-100 px-4 py-3 transition last:border-b-0 hover:bg-gray-50">

                                <input type="checkbox"
                                       name="project_ids[]"
                                       value="{{ $project->id }}"
                                       class="project-checkbox mt-1 h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-400"
                                       {{ in_array((int) $project->id, $selectedProjectIds, true) ? 'checked' : '' }}>

                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $project->project_code }} - {{ $project->project_name }}
                                    </div>

                                    @if($project->location)
                                        <div class="mt-0.5 text-xs text-gray-500">
                                            {{ $project->location }}
                                        </div>
                                    @endif
                                </div>

                            </label>

                        @empty

                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                No projects available.
                            </div>

                        @endforelse

                    </div>
                </div>

            </div>
        </div>

        {{-- Security --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Security</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Password changes are managed separately for better security and auditing.
                </p>
            </div>

            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <div class="text-sm font-semibold text-gray-800">
                        Password Management
                    </div>

                    <div class="mt-1 text-xs text-gray-500">
                        @if($user->password_changed_at)
                            Last changed {{ $user->password_changed_at->format('d M Y, h:i A') }}
                        @else
                            Password change history is not yet available for this user.
                        @endif
                    </div>
                </div>

                <a href="{{ route('users.password', $user->id) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Manage Password
                </a>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    Additional Information
                </h2>
            </div>

            <div class="p-5">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                    Remarks
                </label>

                <textarea name="remarks"
                          rows="3"
                          placeholder="Optional notes about this user..."
                          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-500 focus:ring-2 focus:ring-gray-200">{{ old('remarks', $user->remarks) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">

            <a href="{{ route('users.show', $user->id) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Update User
            </button>

        </div>

    </form>
</div>

@php
    $designationMap = $departments->mapWithKeys(function ($department) {
        return [
            (string) $department->id => $department->designations
                ->map(function ($designation) {
                    return [
                        'id' => $designation->id,
                        'name' => $designation->name,
                    ];
                })
                ->values(),
        ];
    });
@endphp

<script>
    const designationMap = @json($designationMap);

    let selectedDesignationId = @json(
        (string) old(
            'employee_designation_id',
            $user->employee_designation_id ?? ''
        )
    );

    function filterDesignations(clearSelection = false) {
        const departmentSelect = document.getElementById('department_id');
        const designationSelect = document.getElementById('employee_designation_id');

        if (clearSelection) {
            selectedDesignationId = '';
        }

        const departmentId = departmentSelect.value;
        const designations = designationMap[departmentId] || [];

        designationSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = departmentId
            ? 'Select Designation'
            : 'Select Department First';

        designationSelect.appendChild(placeholder);

        designations.forEach(function (designation) {
            const option = document.createElement('option');

            option.value = designation.id;
            option.textContent = designation.name;

            if (String(designation.id) === String(selectedDesignationId)) {
                option.selected = true;
            }

            designationSelect.appendChild(option);
        });

        designationSelect.disabled = !departmentId;
    }

    function toggleProjectSelection() {
        const scope = document.getElementById('project_access_scope').value;
        const section = document.getElementById('project-selection-section');

        if (scope === 'all') {
            section.classList.add('hidden');
        } else {
            section.classList.remove('hidden');
        }
    }

    function toggleAllProjects() {
        const checkboxes = Array.from(
            document.querySelectorAll('.project-checkbox')
        );

        if (!checkboxes.length) {
            return;
        }

        const allChecked = checkboxes.every(function (checkbox) {
            return checkbox.checked;
        });

        checkboxes.forEach(function (checkbox) {
            checkbox.checked = !allChecked;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        filterDesignations(false);
        toggleProjectSelection();
    });
</script>

@endsection
