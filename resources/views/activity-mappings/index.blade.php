@extends('layouts.app')

@section('content')

<x-rds.resource.index
    title="Activity Mapping Master"
    description="Manage RH cost code mappings, Odoo readiness and activity intelligence."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Activity Mappings'],
    ]"
    :paginator="$activityMappings"
>
    <x-slot name="actions">
        <x-rds.button href="{{ route('activity-mappings.create') }}">
            + Add Mapping
        </x-rds.button>
    </x-slot>

    @if(session('success'))
        <x-rds.alert type="success">
            {{ session('success') }}
        </x-rds.alert>
    @endif

    @if($errors->any())
        <x-rds.alert type="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </x-rds.alert>
    @endif

    <x-slot name="toolbar">
        <div class="space-y-4">
            <x-rds.filter-bar action="{{ route('activity-mappings.index') }}">
                <div class="md:col-span-2">
                    <x-rds.input
                        name="search"
                        label="Search"
                        value="{{ request('search') }}"
                        placeholder="Search RH code, activity, unit or Odoo type..."
                    />
                </div>

                <x-rds.select name="division_id" label="Division">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @selected(request('division_id') == $division->id)>
                            {{ $division->code }} - {{ $division->name }}
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
                        href="{{ route('activity-mappings.index') }}"
                    >
                        Reset
                    </x-rds.button>
                </x-slot>
            </x-rds.filter-bar>

            <x-rds.card>
                <form
                    method="POST"
                    action="{{ route('activity-mappings.import') }}"
                    enctype="multipart/form-data"
                    class="flex flex-col gap-3 md:flex-row md:items-end"
                >
                    @csrf

                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Import Activity Mappings
                        </label>

                        <input
                            type="file"
                            name="file"
                            accept=".xlsx,.xls"
                            required
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                        >
                    </div>

                    <x-rds.button type="submit" variant="success">
                        Import Excel
                    </x-rds.button>
                </form>
            </x-rds.card>
        </div>
    </x-slot>

    @if($activityMappings->count())

        <x-rds.table>
            <x-slot name="head">
                <x-rds.table-th>#</x-rds.table-th>
                <x-rds.table-th>Division</x-rds.table-th>
                <x-rds.table-th>RH Code</x-rds.table-th>
                <x-rds.table-th>Activity</x-rds.table-th>
                <x-rds.table-th>Unit</x-rds.table-th>
                <x-rds.table-th>Odoo Type</x-rds.table-th>
                <x-rds.table-th>Status</x-rds.table-th>
                <x-rds.table-th align="right">Actions</x-rds.table-th>
            </x-slot>

            @foreach($activityMappings as $mapping)
                <tr class="hover:bg-gray-50">
                    <x-rds.table-td>
                        {{ $activityMappings->firstItem() + $loop->index }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="font-semibold text-gray-900">
                            {{ $mapping->division?->code ?? '-' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $mapping->division?->name ?? '-' }}
                        </div>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <x-rds.badge variant="info">
                            {{ $mapping->rh_cost_code }}
                        </x-rds.badge>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="font-semibold text-gray-900">
                            {{ $mapping->activity_name }}
                        </div>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        {{ $mapping->unit ?? '-' }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        {{ $mapping->odoo_type ?? '-' }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        @if($mapping->is_active)
                            <x-rds.badge variant="success">Active</x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">Inactive</x-rds.badge>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td align="right">
                        <x-rds.resource.action-menu>
                            <x-rds.resource.action-item
                                href="{{ route('activity-mappings.edit', $mapping) }}"
                            >
                                Edit
                            </x-rds.resource.action-item>
                        </x-rds.resource.action-menu>
                    </x-rds.table-td>
                </tr>
            @endforeach
        </x-rds.table>

    @else

        <x-rds.empty-state
            title="No activity mappings found"
            message="Create your first mapping, import Excel data, or adjust your filters."
        >
            <x-slot name="action">
                <x-rds.button href="{{ route('activity-mappings.create') }}">
                    Add Mapping
                </x-rds.button>
            </x-slot>
        </x-rds.empty-state>

    @endif

</x-rds.resource.index>

@endsection