@extends('layouts.app')

@section('content')

<x-rds.resource.index
    title="Registered Contractors"
    description="Manage contractor profiles, work divisions, locations and compliance details."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Labour & Contractors'],
        ['label' => 'Contractors'],
    ]"
    :paginator="$contractors"
>
    <x-slot name="actions">
        <x-rds.button href="{{ route('contractors.create') }}">
            + Register Contractor
        </x-rds.button>
    </x-slot>

    @if(session('success'))
        <x-rds.alert type="success">
            {{ session('success') }}
        </x-rds.alert>
    @endif

    <x-slot name="toolbar">
    <x-rds.filter-bar action="{{ route('contractors.index') }}">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <x-rds.input
                name="search"
                label="Search"
                value="{{ request('search') }}"
                placeholder="Search contractor..."
            />

            <x-rds.select name="activity_division_id" label="Work Division">
                <option value="">All Work Divisions</option>
                @foreach($activityDivisions as $division)
                    <option value="{{ $division->id }}" @selected(request('activity_division_id') == $division->id)>
                        {{ $division->name }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.select name="city" label="City">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" @selected(request('city') == $city)>
                        {{ $city }}
                    </option>
                @endforeach
            </x-rds.select>

            <x-rds.select name="status" label="Status">
                <option value="">All Status</option>
                <option value="Active" @selected(request('status') === 'Active')>Active</option>
                <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
            </x-rds.select>

        </div>

        <x-slot name="actions">
            <x-rds.button type="submit">Filter</x-rds.button>

            <x-rds.button
                variant="secondary"
                href="{{ route('contractors.index') }}"
            >
                Reset
            </x-rds.button>
        </x-slot>

    </x-rds.filter-bar>
</x-slot>

    @if($contractors->count())

        <x-rds.table>
            <x-slot name="head">
                <x-rds.table-th>#</x-rds.table-th>
                <x-rds.table-th>Code</x-rds.table-th>
                <x-rds.table-th>Contractor</x-rds.table-th>
                <x-rds.table-th>Work Divisions</x-rds.table-th>
                <x-rds.table-th>City</x-rds.table-th>
                <x-rds.table-th>Mobile</x-rds.table-th>
                <x-rds.table-th>Status</x-rds.table-th>
                <x-rds.table-th align="right">Actions</x-rds.table-th>
            </x-slot>

            @foreach($contractors as $contractor)
                <tr class="hover:bg-gray-50">

                    <x-rds.table-td>
                        {{ $contractors->firstItem() + $loop->index }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <span class="inline-flex whitespace-nowrap rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $contractor->contractor_code ?? 'CONT-' . str_pad($contractor->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="font-semibold text-gray-900">
                            {{ $contractor->contractor_name }}
                        </div>

                        @if($contractor->company_name)
                            <div class="text-xs text-gray-500">
                                {{ $contractor->company_name }}
                            </div>
                        @endif

                        @if($contractor->is_preferred)
                            <div class="mt-1 text-xs font-semibold text-yellow-600">
                                ★ Preferred
                            </div>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="flex flex-wrap gap-1">
                            @forelse($contractor->divisions->take(3) as $division)
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                    {{ $division->name }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-400">-</span>
                            @endforelse

                            @if($contractor->divisions->count() > 3)
                                <span class="inline-flex rounded-full bg-gray-50 px-2 py-1 text-xs font-semibold text-gray-500">
                                    +{{ $contractor->divisions->count() - 3 }} more
                                </span>
                            @endif
                        </div>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="text-sm text-gray-900">
                            {{ $contractor->city ?: '-' }}
                        </div>

                        @if($contractor->state)
                            <div class="text-xs text-gray-500">
                                {{ $contractor->state }}
                            </div>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td>
    <div class="text-sm font-medium text-gray-900">
        {{ $contractor->mobile ?: '-' }}
    </div>

    @if($contractor->alternate_mobile)
        <div class="mt-1 text-xs text-gray-500">
            Alt: {{ $contractor->alternate_mobile }}
        </div>
    @endif
</x-rds.table-td>

                    <x-rds.table-td>
                        @if($contractor->status === 'Active')
                            <x-rds.badge variant="success">Active</x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">Inactive</x-rds.badge>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td align="right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('contractors.show', $contractor) }}"
                               class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200">
                                View
                            </a>

                            <a href="{{ route('contractors.edit', $contractor) }}"
                               class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                Edit
                            </a>
                        </div>
                    </x-rds.table-td>

                </tr>
            @endforeach
        </x-rds.table>

    @else

        <x-rds.empty-state
            title="No contractors found"
            message="Register contractors and map them to work divisions for DPR and labour workflows."
        >
            <x-slot name="action">
                <x-rds.button href="{{ route('contractors.create') }}">
                    Register Contractor
                </x-rds.button>
            </x-slot>
        </x-rds.empty-state>

    @endif

</x-rds.resource.index>

@endsection