@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Attendance Statuses"
    subtitle="Manage attendance status codes and attendance behaviour."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('attendance-statuses.create') }}"
                variant="primary"
            >
                Add Attendance Status
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<x-rds.filter-bar
    method="GET"
    action="{{ route('attendance-statuses.index') }}"
>
    <x-rds.input
        name="search"
        label="Search"
        placeholder="Search code or status name"
        :value="request('search')"
    />

    <x-rds.select
        name="status"
        label="Status"
    >
        <option value="">All Statuses</option>
        <option value="active" @selected(request('status') === 'active')>
            Active
        </option>
        <option value="inactive" @selected(request('status') === 'inactive')>
            Inactive
        </option>
    </x-rds.select>

    <x-slot:actions>
        <x-rds.button type="submit" variant="secondary">
            Filter
        </x-rds.button>

        <x-rds.button
    href="{{ route('attendance-statuses.index') }}"
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
                        Attendance Status
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Present
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Absent
                    </th>

                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Payable Factor
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

                @forelse($attendanceStatuses as $attendanceStatus)

                    <tr class="hover:bg-gray-50">

                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ $attendanceStatuses->firstItem() + $loop->index }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-semibold text-gray-900">
                                {{ $attendanceStatus->code }}
                            </span>

                            @if($attendanceStatus->is_system)
                                <span class="ml-1 text-xs text-gray-500">
                                    System
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ $attendanceStatus->name }}
                            </div>

                            @if($attendanceStatus->short_name)
                                <div class="text-xs text-gray-500">
                                    {{ $attendanceStatus->short_name }}
                                </div>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($attendanceStatus->counts_as_present)
                                <x-rds.badge variant="success">
                                    Yes
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    No
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($attendanceStatus->counts_as_absent)
                                <x-rds.badge variant="danger">
                                    Yes
                                </x-rds.badge>
                            @else
                                <x-rds.badge variant="secondary">
                                    No
                                </x-rds.badge>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">
                            {{ number_format((float) $attendanceStatus->payable_factor, 2) }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if($attendanceStatus->is_active)
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
                                    href="{{ route('attendance-statuses.show', $attendanceStatus) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    View
                                </x-rds.button>

                                @if(auth()->user()->hasPermission('labour_master_data.manage'))

                                    <x-rds.button
                                        href="{{ route('attendance-statuses.edit', $attendanceStatus) }}"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Edit
                                    </x-rds.button>

                                    @if($attendanceStatus->canBeDeactivated() || ! $attendanceStatus->is_active)

                                        <form
                                            method="POST"
                                            action="{{ route('attendance-statuses.toggle-status', $attendanceStatus) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <x-rds.button
                                                type="submit"
                                                variant="{{ $attendanceStatus->is_active ? 'danger' : 'success' }}"
                                                size="sm"
                                            >
                                                {{ $attendanceStatus->is_active ? 'Deactivate' : 'Activate' }}
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
                            No attendance statuses found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($attendanceStatuses->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $attendanceStatuses->links() }}
        </div>
    @endif

</x-rds.card>

@endsection