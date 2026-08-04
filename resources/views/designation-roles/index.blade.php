@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Designation Roles"
    subtitle="Manage labour designations and map them to Trade / Manpower Categories and Skill Categories."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('designation-roles.create') }}"
                variant="primary"
            >
                Add Designation Role
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<x-rds.filter-bar
    method="GET"
    action="{{ route('designation-roles.index') }}"
>
    <x-rds.input
        name="search"
        label="Search"
        placeholder="Search code, designation, trade, or skill"
        :value="request('search')"
    />

    <x-rds.select
        name="labour_type_id"
        label="Trade / Manpower Category"
    >
        <option value="">All Trade Categories</option>

        @foreach($labourTypes as $labourType)
            <option
                value="{{ $labourType->id }}"
                @selected((string) request('labour_type_id') === (string) $labourType->id)
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
                @selected((string) request('skill_category_id') === (string) $skillCategory->id)
            >
                {{ $skillCategory->name }}
            </option>
        @endforeach
    </x-rds.select>

    <x-rds.select
        name="status"
        label="Status"
    >
        <option value="">All Statuses</option>

        <option
            value="active"
            @selected(request('status') === 'active')
        >
            Active
        </option>

        <option
            value="inactive"
            @selected(request('status') === 'inactive')
        >
            Inactive
        </option>
    </x-rds.select>

    <x-slot:actions>
        <x-rds.button
            type="submit"
            variant="secondary"
        >
            Filter
        </x-rds.button>

        <x-rds.button
            href="{{ route('designation-roles.index') }}"
            variant="secondary"
        >
            Reset
        </x-rds.button>
    </x-slot:actions>
</x-rds.filter-bar>

<x-rds.card :padding="false">

    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[980px] table-fixed divide-y divide-gray-200">

            <colgroup>
                <col class="w-[52px]">
                <col class="w-[125px]">
                <col class="w-[220px]">
                <col class="w-[220px]">
                <col class="w-[125px]">
                <col class="w-[95px]">
                <col class="w-[85px]">
                <col class="w-[250px]">
            </colgroup>

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        #
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Code
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Designation Role
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Trade / Manpower Category
                    </th>

                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Skill Category
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Record Type
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

                @forelse($designationRoles as $designationRole)

                    <tr class="align-top hover:bg-gray-50">

                        <td class="px-3 py-3 text-sm text-gray-500">
                            {{ $designationRoles->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-3 text-sm font-semibold text-gray-900 break-words">
                            {{ $designationRole->code }}
                        </td>

                        <td class="px-3 py-3">
    <div class="break-words font-medium leading-5 text-gray-900">
        {{ $designationRole->name }}
    </div>
</td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $designationRole->labourType?->labour_type_name ?? 'General / Not Mapped' }}
                        </td>

                        <td class="px-3 py-3 text-sm leading-5 text-gray-700 break-words">
                            {{ $designationRole->skillCategory?->name ?? 'Not Mapped' }}
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($designationRole->is_system)
                                <x-rds.badge variant="warning">
                                    System
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    Custom
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($designationRole->is_active)
                                <x-rds.badge variant="success">
                                    Active
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="danger">
                                    Inactive
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-right">

                            <div class="flex flex-nowrap items-center justify-end gap-1.5">

                                <x-rds.button
    href="{{ route('designation-roles.show', $designationRole) }}"
    variant="secondary"
    size="sm"
    class="!px-3 !py-1.5 !text-xs"
>
    View
</x-rds.button>

                                @if(
                                    auth()->user()->hasPermission('labour_master_data.manage')
                                    && ! $designationRole->is_system
                                )

                                    <x-rds.button
                                        href="{{ route('designation-roles.edit', $designationRole) }}"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Edit
                                    </x-rds.button>

                                    <form
    method="POST"
    action="{{ route('designation-roles.toggle-status', $designationRole) }}"
    class="shrink-0"
>
                                        @csrf
                                        @method('PATCH')

                                        <x-rds.button
    type="submit"
    variant="{{ $designationRole->is_active ? 'danger' : 'success' }}"
    size="sm"
    class="!px-3 !py-1.5 !text-xs"
>
    {{ $designationRole->is_active ? 'Deactivate' : 'Activate' }}
</x-rds.button>
                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="8"
                            class="px-4 py-10 text-center text-sm text-gray-500"
                        >
                            No designation roles found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($designationRoles->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $designationRoles->links() }}
        </div>
    @endif

</x-rds.card>

@endsection