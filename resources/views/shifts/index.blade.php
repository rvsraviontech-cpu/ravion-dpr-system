@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Shifts"
    subtitle="Manage labour shifts, timings, normal working hours, and overnight shift behaviour."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('labour_master_data.manage'))
            <x-rds.button
                href="{{ route('shifts.create') }}"
                variant="primary"
            >
                Add Shift
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

<x-rds.filter-bar
    method="GET"
    action="{{ route('shifts.index') }}"
>
    <x-rds.input
        name="search"
        label="Search"
        placeholder="Search code or shift name"
        :value="request('search')"
    />

    <x-rds.select
        name="crosses_midnight"
        label="Overnight Shift"
    >
        <option value="">All Shifts</option>

        <option
            value="yes"
            @selected(request('crosses_midnight') === 'yes')
        >
            Crosses Midnight
        </option>

        <option
            value="no"
            @selected(request('crosses_midnight') === 'no')
        >
            Same Day Shift
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
            href="{{ route('shifts.index') }}"
            variant="secondary"
        >
            Reset
        </x-rds.button>
    </x-slot:actions>
</x-rds.filter-bar>

<x-rds.card :padding="false">

    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[950px] table-fixed divide-y divide-gray-200">

            <colgroup>
                <col class="w-[50px]">
                <col class="w-[120px]">
                <col class="w-[210px]">
                <col class="w-[115px]">
                <col class="w-[115px]">
                <col class="w-[105px]">
                <col class="w-[120px]">
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
                        Shift Name
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Start Time
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        End Time
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Normal Hours
                    </th>

                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Overnight
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

                @forelse($shifts as $shift)

                    <tr class="align-middle hover:bg-gray-50">

                        <td class="px-3 py-3 text-sm text-gray-500">
                            {{ $shifts->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-3 text-sm font-semibold text-gray-900 break-words">
                            {{ $shift->code }}
                        </td>

                        <td class="px-3 py-3">
                            <div class="break-words font-medium leading-5 text-gray-900">
                                {{ $shift->name }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center text-sm text-gray-700">
                            {{ $shift->start_time
                                ? \Carbon\Carbon::createFromFormat('H:i:s', $shift->start_time)->format('h:i A')
                                : '—' }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm text-gray-700">
                            {{ $shift->end_time
                                ? \Carbon\Carbon::createFromFormat('H:i:s', $shift->end_time)->format('h:i A')
                                : '—' }}
                        </td>

                        <td class="px-3 py-3 text-center text-sm font-medium text-gray-800">
                            {{ number_format((float) $shift->normal_hours, 2) }}
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($shift->crosses_midnight)
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
                            @if($shift->is_system)
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
                            @if($shift->is_active)
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
                                    href="{{ route('shifts.show', $shift) }}"
                                    variant="secondary"
                                    size="sm"
                                    class="!px-3 !py-1.5 !text-xs"
                                >
                                    View
                                </x-rds.button>

                                @if(auth()->user()->hasPermission('labour_master_data.manage'))

    <x-rds.button
        href="{{ route('shifts.edit', $shift) }}"
        variant="secondary"
        size="sm"
        class="!px-3 !py-1.5 !text-xs"
    >
        Edit
    </x-rds.button>

    @if(! $shift->is_system)

        <form
            method="POST"
            action="{{ route('shifts.toggle-status', $shift) }}"
            class="shrink-0"
        >
            @csrf
            @method('PATCH')

            <x-rds.button
                type="submit"
                variant="{{ $shift->is_active ? 'danger' : 'success' }}"
                size="sm"
                class="!px-3 !py-1.5 !text-xs"
            >
                {{ $shift->is_active ? 'Deactivate' : 'Activate' }}
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
                            colspan="10"
                            class="px-4 py-10 text-center text-sm text-gray-500"
                        >
                            No shifts found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($shifts->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $shifts->links() }}
        </div>
    @endif

</x-rds.card>

@endsection