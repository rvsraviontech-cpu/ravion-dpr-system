@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Manpower Sources"
    subtitle="Manage the source and engagement type of labour used across projects and attendance."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('manpower-sources.create') }}"
                variant="primary"
            >
                Add Manpower Source
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<x-rds.filter-bar
    method="GET"
    action="{{ route('manpower-sources.index') }}"
>
    <x-rds.input
        name="search"
        label="Search"
        placeholder="Search code or manpower source"
        :value="request('search')"
    />

    <x-rds.select
        name="contractor_requirement"
        label="Contractor Requirement"
    >
        <option value="">All Sources</option>

        <option
            value="required"
            @selected(request('contractor_requirement') === 'required')
        >
            Contractor Required
        </option>

        <option
            value="not_required"
            @selected(request('contractor_requirement') === 'not_required')
        >
            Contractor Not Required
        </option>
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
            href="{{ route('manpower-sources.index') }}"
            variant="secondary"
        >
            Reset
        </x-rds.button>
    </x-slot:actions>
</x-rds.filter-bar>

<x-rds.card :padding="false">

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        #
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Code
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Manpower Source
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Contractor Required
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Record Type
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Sort Order
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Status
                    </th>

                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">

                @forelse($manpowerSources as $manpowerSource)

                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ $manpowerSources->firstItem() + $loop->index }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $manpowerSource->code }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ $manpowerSource->name }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($manpowerSource->requires_contractor)
                                <x-rds.badge variant="warning">
                                    Required
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    Not Required
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($manpowerSource->is_system)
                                <x-rds.badge variant="warning">
                                    System
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    Custom
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-700">
                            {{ $manpowerSource->sort_order }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($manpowerSource->is_active)
                                <x-rds.badge variant="success">
                                    Active
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="danger">
                                    Inactive
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right">

                            <div class="flex items-center justify-end gap-2">

                                <x-rds.button
                                    href="{{ route('manpower-sources.show', $manpowerSource) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    View
                                </x-rds.button>

                                @if(auth()->user()->hasPermission('labour_master_data.manage'))

                                    @if(! $manpowerSource->is_system)
    <x-rds.button
        href="{{ route('manpower-sources.edit', $manpowerSource) }}"
        variant="secondary"
        size="sm"
    >
        Edit
    </x-rds.button>
@endif

                                    @if($manpowerSource->canBeDeactivated() || ! $manpowerSource->is_active)

                                        <form
                                            method="POST"
                                            action="{{ route('manpower-sources.toggle-status', $manpowerSource) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <x-rds.button
                                                type="submit"
                                                variant="{{ $manpowerSource->is_active ? 'danger' : 'success' }}"
                                                size="sm"
                                            >
                                                {{ $manpowerSource->is_active ? 'Deactivate' : 'Activate' }}
                                            </x-rds.button>
                                        </form>

                                    @endif

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
                            No manpower sources found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($manpowerSources->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $manpowerSources->links() }}
        </div>
    @endif

</x-rds.card>

@endsection