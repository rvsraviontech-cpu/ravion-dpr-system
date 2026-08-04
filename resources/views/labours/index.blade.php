@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Labour Master"
    subtitle="Manage individual labour profiles, classifications, contractor assignments, and employment status."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_masters.create'))
            <x-rds.button
                href="{{ route('labours.create') }}"
                variant="primary"
            >
                Add Labour
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<form
    method="GET"
    action="{{ route('labours.index') }}"
    class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">

        <x-rds.input
            name="search"
            label="Search"
            placeholder="Code, name, mobile, or identity number"
            :value="request('search')"
        />

        <x-rds.select
            name="manpower_source_id"
            label="Manpower Source"
        >
            <option value="">All Sources</option>

            @foreach($manpowerSources as $manpowerSource)
                <option
                    value="{{ $manpowerSource->id }}"
                    @selected(
                        (string) request('manpower_source_id')
                        === (string) $manpowerSource->id
                    )
                >
                    {{ $manpowerSource->name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="labour_category_id"
            label="Labour Category"
        >
            <option value="">All Categories</option>

            @foreach($labourCategories as $labourCategory)
                <option
                    value="{{ $labourCategory->id }}"
                    @selected(
                        (string) request('labour_category_id')
                        === (string) $labourCategory->id
                    )
                >
                    {{ $labourCategory->category_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="labour_type_id"
            label="Trade / Manpower Category"
        >
            <option value="">All Trades</option>

            @foreach($labourTypes as $labourType)
                <option
                    value="{{ $labourType->id }}"
                    @selected(
                        (string) request('labour_type_id')
                        === (string) $labourType->id
                    )
                >
                    {{ $labourType->labour_type_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="skill_category_id"
            label="Skill Category"
        >
            <option value="">All Skill Categories</option>

            @foreach($skillCategories as $skillCategory)
                <option
                    value="{{ $skillCategory->id }}"
                    @selected(
                        (string) request('skill_category_id')
                        === (string) $skillCategory->id
                    )
                >
                    {{ $skillCategory->name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="designation_role_id"
            label="Designation Role"
        >
            <option value="">All Designations</option>

            @foreach($designationRoles as $designationRole)
                <option
                    value="{{ $designationRole->id }}"
                    @selected(
                        (string) request('designation_role_id')
                        === (string) $designationRole->id
                    )
                >
                    {{ $designationRole->name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="contractor_id"
            label="Contractor"
        >
            <option value="">All Contractors</option>

            @foreach($contractors as $contractor)
                <option
                    value="{{ $contractor->id }}"
                    @selected(
                        (string) request('contractor_id')
                        === (string) $contractor->id
                    )
                >
                    {{ $contractor->contractor_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="current_project_id"
            label="Current Project"
        >
            <option value="">All Projects</option>

            @foreach($projects as $project)
                <option
                    value="{{ $project->id }}"
                    @selected(
                        (string) request('current_project_id')
                        === (string) $project->id
                    )
                >
                    {{ $project->project_name }}
                </option>
            @endforeach
        </x-rds.select>

        <x-rds.select
            name="employment_status"
            label="Employment Status"
        >
            <option value="">All Employment Statuses</option>

            <option value="active" @selected(request('employment_status') === 'active')>
                Active
            </option>

            <option value="inactive" @selected(request('employment_status') === 'inactive')>
                Inactive
            </option>

            <option value="on_leave" @selected(request('employment_status') === 'on_leave')>
                On Leave
            </option>

            <option value="exited" @selected(request('employment_status') === 'exited')>
                Exited
            </option>

            <option value="suspended" @selected(request('employment_status') === 'suspended')>
                Suspended
            </option>
        </x-rds.select>

        <x-rds.select
            name="residency_status"
            label="Residency"
        >
            <option value="">All Residency Types</option>

            <option value="local" @selected(request('residency_status') === 'local')>
                Local
            </option>

            <option value="non_local" @selected(request('residency_status') === 'non_local')>
                Non-Local
            </option>

            <option value="not_specified" @selected(request('residency_status') === 'not_specified')>
                Not Specified
            </option>
        </x-rds.select>

        <x-rds.select
            name="record_status"
            label="Record Status"
        >
            <option value="">All Records</option>

            <option value="active" @selected(request('record_status') === 'active')>
                Active Records
            </option>

            <option value="inactive" @selected(request('record_status') === 'inactive')>
                Inactive Records
            </option>
        </x-rds.select>

    </div>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">

        <x-rds.button
            type="submit"
            variant="primary"
            class="!px-4 !py-2 !text-sm"
        >
            Filter
        </x-rds.button>

        <x-rds.button
            href="{{ route('labours.index') }}"
            variant="secondary"
            class="!px-4 !py-2 !text-sm"
        >
            Reset
        </x-rds.button>

    </div>
</form>

<x-rds.card :padding="false">

    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[1450px] table-fixed divide-y divide-gray-200">

            <colgroup>
                <col class="w-[50px]">
                <col class="w-[130px]">
                <col class="w-[210px]">
                <col class="w-[140px]">
                <col class="w-[150px]">
                <col class="w-[170px]">
                <col class="w-[135px]">
                <col class="w-[165px]">
                <col class="w-[170px]">
                <col class="w-[150px]">
                <col class="w-[110px]">
                <col class="w-[220px]">
            </colgroup>

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        #
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Labour Code
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Labour Name
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Mobile
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Manpower Source
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Trade / Category
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Skill
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Designation
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Contractor
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Current Project
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Status
                    </th>

                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">

                @forelse($labours as $labour)

                    <tr class="align-middle hover:bg-gray-50">

                        <td class="px-3 py-3 text-sm text-gray-500">
                            {{ $labours->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-3 text-sm font-semibold text-gray-900 break-words">
                            {{ $labour->labour_code }}
                        </td>

                        <td class="px-3 py-3">
                            <div class="flex items-center gap-3">

                                @if($labour->photo_path)
                                    <img
                                        src="{{ asset('storage/' . $labour->photo_path) }}"
                                        alt="{{ $labour->full_name }}"
                                        class="h-9 w-9 shrink-0 rounded-full border border-gray-200 object-cover"
                                    >
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                                        {{ strtoupper(
                                            mb_substr($labour->full_name, 0, 1)
                                        ) }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="break-words font-medium leading-5 text-gray-900">
                                        {{ $labour->full_name }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $labour->gender?->name ?? 'Gender not specified' }}
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td class="px-3 py-3 text-sm text-gray-700">
                            {{ $labour->mobile ?: '—' }}
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $labour->manpowerSource?->name ?? '—' }}
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            <div>
                                {{ $labour->labourType?->labour_type_name ?? 'Not Mapped' }}
                            </div>

                            @if($labour->labourCategory)
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $labour->labourCategory->category_name }}
                                </div>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $labour->skillCategory?->name ?? '—' }}
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $labour->designationRole?->name ?? '—' }}
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $labour->contractor?->contractor_name ?? 'Company / Direct' }}
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $labour->currentProject?->project_name ?? 'Not Assigned' }}
                        </td>

                        <td class="px-3 py-3 text-center">

                            <div class="flex flex-col items-center gap-1">

                                @if($labour->is_active)
                                    <x-rds.badge variant="success">
                                        Active
                                    </x-rds.badge>
                                @else
                                    <x-rds.badge variant="danger">
                                        Inactive
                                    </x-rds.badge>
                                @endif

                                @php
                                    $employmentVariant = match ($labour->employment_status) {
                                        'active' => 'success',
                                        'on_leave' => 'warning',
                                        'exited' => 'danger',
                                        'suspended' => 'danger',
                                        default => 'secondary',
                                    };

                                    $employmentLabel = match ($labour->employment_status) {
                                        'active' => 'Employed',
                                        'inactive' => 'Inactive',
                                        'on_leave' => 'On Leave',
                                        'exited' => 'Exited',
                                        'suspended' => 'Suspended',
                                        default => ucfirst(
                                            str_replace('_', ' ', $labour->employment_status)
                                        ),
                                    };
                                @endphp

                                <x-rds.badge :variant="$employmentVariant">
                                    {{ $employmentLabel }}
                                </x-rds.badge>

                            </div>

                        </td>

                        <td class="px-3 py-3 text-right">

                            <div class="flex flex-nowrap items-center justify-end gap-1.5">

                                <x-rds.button
                                    href="{{ route('labours.show', $labour) }}"
                                    variant="secondary"
                                    size="sm"
                                    class="!px-3 !py-1.5 !text-xs"
                                >
                                    View
                                </x-rds.button>

                                @if(auth()->user()->hasPermission('labour_masters.edit'))
                                    <x-rds.button
                                        href="{{ route('labours.edit', $labour) }}"
                                        variant="secondary"
                                        size="sm"
                                        class="!px-3 !py-1.5 !text-xs"
                                    >
                                        Edit
                                    </x-rds.button>
                                @endif

                                @if(auth()->user()->hasPermission('labour_masters.toggle_status'))
                                    <form
                                        method="POST"
                                        action="{{ route('labours.toggle-status', $labour) }}"
                                        class="shrink-0"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <x-rds.button
                                            type="submit"
                                            variant="{{ $labour->is_active ? 'danger' : 'success' }}"
                                            size="sm"
                                            class="!px-3 !py-1.5 !text-xs"
                                        >
                                            {{ $labour->is_active ? 'Deactivate' : 'Activate' }}
                                        </x-rds.button>
                                    </form>
                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="12"
                            class="px-4 py-12 text-center"
                        >
                            <div class="text-sm font-medium text-gray-700">
                                No labour profiles found.
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Adjust the filters or add the first labour profile.
                            </div>
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($labours->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $labours->links() }}
        </div>
    @endif

</x-rds.card>

@endsection