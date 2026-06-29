@extends('layouts.app')

@section('content')

<x-rds.resource.index
    title="Activity Master"
    description="Manage DPR activity dropdowns. Cost codes remain hidden from engineers."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Activities'],
    ]"
    :paginator="$activities"
>
    <x-slot name="actions">
        <x-rds.button href="{{ route('activities.create') }}">
            + Create Activity
        </x-rds.button>
    </x-slot>

    @if(session('success'))
        <x-rds.alert type="success">
            {{ session('success') }}
        </x-rds.alert>
    @endif

    <x-slot name="toolbar">
        <x-rds.filter-bar action="{{ route('activities.index') }}">
            <div class="md:col-span-2">
                <x-rds.input
                    name="search"
                    label="Search"
                    value="{{ request('search') }}"
                    placeholder="Search activity, work stage, unit..."
                />
            </div>

            <x-rds.select name="activity_division_id" label="Division">
                <option value="">All Divisions</option>
                @foreach($activityDivisions as $division)
                    <option value="{{ $division->id }}" @selected(request('activity_division_id') == $division->id)>
                        {{ $division->name }}
                    </option>
                @endforeach
            </x-rds.select>

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
                    href="{{ route('activities.index') }}"
                >
                    Reset
                </x-rds.button>
            </x-slot>
        </x-rds.filter-bar>
    </x-slot>

    @if($activities->count())

        <x-rds.table>
            <x-slot name="head">
                <x-rds.table-th>#</x-rds.table-th>
                <x-rds.table-th>Activity</x-rds.table-th>
                <x-rds.table-th>Division</x-rds.table-th>
                <x-rds.table-th>Work Stage</x-rds.table-th>
                <x-rds.table-th>Unit</x-rds.table-th>
                <x-rds.table-th>Status</x-rds.table-th>
                <x-rds.table-th align="right">Actions</x-rds.table-th>
            </x-slot>

            @foreach($activities as $activity)
                <tr class="hover:bg-gray-50">
                    <x-rds.table-td>
    {{ ($activities->currentPage() - 1) * $activities->perPage() + $loop->iteration }}
</x-rds.table-td>

<x-rds.table-td>
    <div class="font-semibold text-gray-900">
        {{ $activity->activity_name }}
    </div>
</x-rds.table-td>

                    <x-rds.table-td>
                        {{ $activity->division->name ?? '-' }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        {{ $activity->work_stage }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <x-rds.badge variant="info">
                            {{ $activity->unit }}
                        </x-rds.badge>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        @if($activity->is_active)
                            <x-rds.badge variant="success">Active</x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">Inactive</x-rds.badge>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td align="right">
                        <x-rds.resource.action-menu>
                            <x-rds.resource.action-item
                                href="{{ route('activities.show', $activity) }}"
                            >
                                View
                            </x-rds.resource.action-item>

                            <x-rds.resource.action-item
                                href="{{ route('activities.edit', $activity) }}"
                            >
                                Edit
                            </x-rds.resource.action-item>

                            <x-rds.resource.action-item
                                x-data
                                @click="$dispatch('open-confirm-modal', 'activity-status-{{ $activity->id }}')"
                            >
                                {{ $activity->is_active ? 'Deactivate' : 'Activate' }}
                            </x-rds.resource.action-item>
                        </x-rds.resource.action-menu>

                        <x-rds.confirm-modal
                            id="activity-status-{{ $activity->id }}"
                            title="{{ $activity->is_active ? 'Deactivate Activity' : 'Activate Activity' }}"
                            message="Are you sure you want to change this activity status?"
                        >
                            <form
                                method="POST"
                                action="{{ route('activities.destroy', $activity) }}"
                            >
                                @csrf
                                @method('DELETE')

                                <x-rds.button
                                    type="submit"
                                    variant="{{ $activity->is_active ? 'danger' : 'success' }}"
                                >
                                    {{ $activity->is_active ? 'Deactivate' : 'Activate' }}
                                </x-rds.button>
                            </form>
                        </x-rds.confirm-modal>
                    </x-rds.table-td>
                </tr>
            @endforeach
        </x-rds.table>

    @else

        <x-rds.empty-state
            title="No activities found"
            message="Create your first activity or adjust your filters."
        >
            <x-slot name="action">
                <x-rds.button href="{{ route('activities.create') }}">
                    Create Activity
                </x-rds.button>
            </x-slot>
        </x-rds.empty-state>

    @endif

</x-rds.resource.index>

@endsection