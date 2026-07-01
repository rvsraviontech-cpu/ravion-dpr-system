@extends('layouts.app')

@section('content')

<x-rds.resource.index
    title="Contractor Service Categories"
    description="Map contractor services to work divisions for real-world contractor selection."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Service Categories'],
    ]"
    :paginator="$serviceCategories"
>
    <x-slot name="actions">
        <x-rds.button href="{{ route('contractor-service-categories.create') }}">
            + New Service Category
        </x-rds.button>
    </x-slot>

    @if(session('success'))
        <x-rds.alert type="success">
            {{ session('success') }}
        </x-rds.alert>
    @endif

    <x-slot name="toolbar">
        <x-rds.filter-bar action="{{ route('contractor-service-categories.index') }}">
            <x-rds.input
                name="search"
                label="Search"
                value="{{ request('search') }}"
                placeholder="Search code, service category or remarks..."
            />

            <x-rds.select name="activity_division_id" label="Work Division">
                <option value="">All Work Divisions</option>
                @foreach($activityDivisions as $division)
                    <option value="{{ $division->id }}" @selected(request('activity_division_id') == $division->id)>
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
                <x-rds.button type="submit">Filter</x-rds.button>

                <x-rds.button
                    variant="secondary"
                    href="{{ route('contractor-service-categories.index') }}"
                >
                    Reset
                </x-rds.button>
            </x-slot>
        </x-rds.filter-bar>
    </x-slot>

    @if($serviceCategories->count())
        <x-rds.table>
            <x-slot name="head">
                <x-rds.table-th>#</x-rds.table-th>
                <x-rds.table-th>Code</x-rds.table-th>
                <x-rds.table-th>Service Category</x-rds.table-th>
                <x-rds.table-th>Work Division</x-rds.table-th>
                <x-rds.table-th>Status</x-rds.table-th>
                <x-rds.table-th align="right">Actions</x-rds.table-th>
            </x-slot>

            @foreach($serviceCategories as $category)
                <tr class="hover:bg-gray-50">
                    <x-rds.table-td>
                        {{ $serviceCategories->firstItem() + $loop->index }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <x-rds.badge variant="info">
                            {{ $category->code ?: '-' }}
                        </x-rds.badge>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="font-semibold text-gray-900">
                            {{ $category->name }}
                        </div>

                        @if($category->remarks)
                            <div class="mt-1 text-xs text-gray-500">
                                {{ Str::limit($category->remarks, 60) }}
                            </div>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td>
                        {{ $category->division?->code ? $category->division->code . ' - ' : '' }}
                        {{ $category->division?->name ?? '-' }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        @if($category->is_active)
                            <x-rds.badge variant="success">Active</x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">Inactive</x-rds.badge>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td align="right">
                        <x-rds.action-menu
                            :show="route('contractor-service-categories.show', $category)"
                            :edit="route('contractor-service-categories.edit', $category)"
                            :toggle="route('contractor-service-categories.destroy', $category)"
                            :active="$category->is_active"
                        />
                    </x-rds.table-td>
                </tr>
            @endforeach
        </x-rds.table>
    @else
        <x-rds.empty-state
            title="No service categories found"
            message="Create service categories such as Electrical Contractor, Mason Contractor, Earthwork Contractor."
        >
            <x-slot name="action">
                <x-rds.button href="{{ route('contractor-service-categories.create') }}">
                    Create Service Category
                </x-rds.button>
            </x-slot>
        </x-rds.empty-state>
    @endif

</x-rds.resource.index>

@endsection