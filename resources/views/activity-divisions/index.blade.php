@extends('layouts.app')

@section('content')

<x-rds.resource.index
    title="Activity Divisions"
    description="Manage construction activity groups used throughout DPR, BOQ and Planning."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Activity Divisions'],
    ]"
    :paginator="$activityDivisions"
>
    <x-slot name="actions">
        <x-rds.button href="{{ route('activity-divisions.create') }}">
            + New Division
        </x-rds.button>
    </x-slot>

    @if(session('success'))
        <x-rds.alert type="success">
            {{ session('success') }}
        </x-rds.alert>
    @endif

    <x-slot name="toolbar">
        <x-rds.filter-bar action="{{ route('activity-divisions.index') }}">
            <div class="md:col-span-2">
                <x-rds.input
                    name="search"
                    label="Search"
                    value="{{ request('search') }}"
                    placeholder="Search code, division or remarks..."
                />
            </div>

            <x-rds.select name="status" label="Status">
                <option value="">All Status</option>
                <option value="1" @selected(request('status') === '1')>Active</option>
                <option value="0" @selected(request('status') === '0')>Inactive</option>
            </x-rds.select>

            <x-slot name="actions">
                <x-rds.button type="submit">
                    Filter
                </x-rds.button>

                <x-rds.button
                    variant="secondary"
                    href="{{ route('activity-divisions.index') }}"
                >
                    Reset
                </x-rds.button>
            </x-slot>
        </x-rds.filter-bar>
    </x-slot>

    @if($activityDivisions->count())

        <x-rds.table>
            <x-slot name="head">
                <x-rds.table-th>#</x-rds.table-th>
                <x-rds.table-th>Code</x-rds.table-th>
                <x-rds.table-th>Division</x-rds.table-th>
                <x-rds.table-th>Sequence</x-rds.table-th>
                <x-rds.table-th>Status</x-rds.table-th>
                <x-rds.table-th align="right">Actions</x-rds.table-th>
            </x-slot>

            @foreach($activityDivisions as $division)
                <tr class="hover:bg-gray-50">
                    <x-rds.table-td>
                        {{ $activityDivisions->firstItem() + $loop->index }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <x-rds.badge variant="info">
                            {{ $division->code }}
                        </x-rds.badge>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="font-semibold text-gray-900">
                            {{ $division->name }}
                        </div>

                        @if($division->remarks)
                            <div class="mt-1 text-xs text-gray-500">
                                {{ Str::limit($division->remarks, 60) }}
                            </div>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td>
                        {{ $division->sequence }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        @if($division->is_active)
                            <x-rds.badge variant="success">Active</x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">Inactive</x-rds.badge>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td align="right">
                        <x-rds.action-menu
                            :show="route('activity-divisions.show', $division)"
                            :edit="route('activity-divisions.edit', $division)"
                            :toggle="route('activity-divisions.destroy', $division)"
                            :active="$division->is_active"
                        />
                    </x-rds.table-td>
                </tr>
            @endforeach
        </x-rds.table>

    @else

        <x-rds.empty-state
            title="No activity divisions found"
            message="Create your first activity division or adjust your filters."
        >
            <x-slot name="action">
                <x-rds.button href="{{ route('activity-divisions.create') }}">
                    Create Division
                </x-rds.button>
            </x-slot>
        </x-rds.empty-state>

    @endif

</x-rds.resource.index>

@endsection