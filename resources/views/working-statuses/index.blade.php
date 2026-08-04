@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Working Statuses"
    subtitle="Manage labour working conditions used in attendance, idle manpower reporting, and PMO review."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('working-statuses.create') }}"
                variant="primary"
            >
                Add Working Status
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<x-rds.filter-bar
    method="GET"
    action="{{ route('working-statuses.index') }}"
>
    <x-rds.input
        name="search"
        label="Search"
        placeholder="Search code or working status"
        :value="request('search')"
    />

    <x-rds.select
        name="idle_status"
        label="Idle Classification"
    >
        <option value="">All Classifications</option>

        <option
            value="idle"
            @selected(request('idle_status') === 'idle')
        >
            Counts as Idle
        </option>

        <option
            value="productive"
            @selected(request('idle_status') === 'productive')
        >
            Does Not Count as Idle
        </option>
    </x-rds.select>

    <x-rds.select
        name="reason_requirement"
        label="Reason Requirement"
    >
        <option value="">All Requirements</option>

        <option
            value="required"
            @selected(request('reason_requirement') === 'required')
        >
            Reason Required
        </option>

        <option
            value="not_required"
            @selected(request('reason_requirement') === 'not_required')
        >
            Reason Not Required
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
            href="{{ route('working-statuses.index') }}"
            variant="secondary"
        >
            Reset
        </x-rds.button>
    </x-slot:actions>
</x-rds.filter-bar>

<x-rds.card :padding="false">

    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[920px] table-fixed divide-y divide-gray-200">

            <colgroup>
                <col class="w-[50px]">
                <col class="w-[150px]">
                <col class="w-[240px]">
                <col class="w-[130px]">
                <col class="w-[145px]">
                <col class="w-[100px]">
                <col class="w-[90px]">
                <col class="w-[220px]">
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
                        Working Status
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Counts as Idle
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Reason Required
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

                @forelse($workingStatuses as $workingStatus)

                    <tr class="align-middle hover:bg-gray-50">

                        <td class="px-3 py-3 text-sm text-gray-500">
                            {{ $workingStatuses->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-3 text-sm font-semibold text-gray-900 break-words">
                            {{ $workingStatus->code }}
                        </td>

                        <td class="px-3 py-3">
                            <div class="break-words font-medium leading-5 text-gray-900">
                                {{ $workingStatus->name }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($workingStatus->counts_as_idle)
                                <x-rds.badge variant="warning">
                                    Yes
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    No
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($workingStatus->requires_reason)
                                <x-rds.badge variant="warning">
                                    Required
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    Not Required
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($workingStatus->is_system)
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
                            @if($workingStatus->is_active)
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
                                    href="{{ route('working-statuses.show', $workingStatus) }}"
                                    variant="secondary"
                                    size="sm"
                                    class="!px-3 !py-1.5 !text-xs"
                                >
                                    View
                                </x-rds.button>

                                @if(
                                    auth()->user()->hasPermission('labour_master_data.manage')
                                    && ! $workingStatus->is_system
                                )

                                    <x-rds.button
                                        href="{{ route('working-statuses.edit', $workingStatus) }}"
                                        variant="secondary"
                                        size="sm"
                                        class="!px-3 !py-1.5 !text-xs"
                                    >
                                        Edit
                                    </x-rds.button>

                                    <form
                                        method="POST"
                                        action="{{ route('working-statuses.toggle-status', $workingStatus) }}"
                                        class="shrink-0"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <x-rds.button
                                            type="submit"
                                            variant="{{ $workingStatus->is_active ? 'danger' : 'success' }}"
                                            size="sm"
                                            class="!px-3 !py-1.5 !text-xs"
                                        >
                                            {{ $workingStatus->is_active ? 'Deactivate' : 'Activate' }}
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
                            No working statuses found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($workingStatuses->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $workingStatuses->links() }}
        </div>
    @endif

</x-rds.card>

@endsection