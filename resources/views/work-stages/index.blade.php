@extends('layouts.app')

@section('content')

<x-rds.resource.index
    title="Work Stages"
    description="Construction execution stages used by Activities, DPR, Planning and Reports."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Execution Masters'],
        ['label' => 'Work Stages'],
    ]"
    :paginator="$workStages"
>
    <x-slot name="actions">
        <x-rds.button href="{{ route('work-stages.create') }}">
            + New Work Stage
        </x-rds.button>
    </x-slot>

    @if(session('success'))
        <x-rds.alert type="success">
            {{ session('success') }}
        </x-rds.alert>
    @endif

    <x-slot name="toolbar">
        <x-rds.filter-bar action="{{ route('work-stages.index') }}">
            <div class="md:col-span-2">
                <x-rds.input
                    name="search"
                    label="Search"
                    value="{{ request('search') }}"
                    placeholder="Search code, work stage or remarks..."
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
                    href="{{ route('work-stages.index') }}"
                >
                    Reset
                </x-rds.button>
            </x-slot>
        </x-rds.filter-bar>
    </x-slot>

    @if($workStages->count())

        <x-rds.table>
            <x-slot name="head">
                <x-rds.table-th>#</x-rds.table-th>
                <x-rds.table-th>Code</x-rds.table-th>
                <x-rds.table-th>Work Stage</x-rds.table-th>
                <x-rds.table-th>Sequence</x-rds.table-th>
                <x-rds.table-th>Status</x-rds.table-th>
                <x-rds.table-th align="right">Actions</x-rds.table-th>
            </x-slot>

            @foreach($workStages as $stage)
                <tr class="hover:bg-gray-50">
                    <x-rds.table-td>
                        {{ $workStages->firstItem() + $loop->index }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <x-rds.badge variant="info">
                            {{ $stage->code }}
                        </x-rds.badge>
                    </x-rds.table-td>

                    <x-rds.table-td>
                        <div class="font-semibold text-gray-900">
                            {{ $stage->name }}
                        </div>

                        @if($stage->remarks)
                            <div class="mt-1 text-xs text-gray-500">
                                {{ Str::limit($stage->remarks, 60) }}
                            </div>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td>
                        {{ $stage->sequence }}
                    </x-rds.table-td>

                    <x-rds.table-td>
                        @if($stage->is_active)
                            <x-rds.badge variant="success">Active</x-rds.badge>
                        @else
                            <x-rds.badge variant="danger">Inactive</x-rds.badge>
                        @endif
                    </x-rds.table-td>

                    <x-rds.table-td align="right">
                        <x-rds.action-menu
                            :show="route('work-stages.show', $stage)"
                            :edit="route('work-stages.edit', $stage)"
                            :toggle="route('work-stages.destroy', $stage)"
                            :active="$stage->is_active"
                        />
                    </x-rds.table-td>
                </tr>
            @endforeach
        </x-rds.table>

    @else

        <x-rds.empty-state
            title="No work stages found"
            message="Create your first work stage or adjust your filters."
        >
            <x-slot name="action">
                <x-rds.button href="{{ route('work-stages.create') }}">
                    Create Work Stage
                </x-rds.button>
            </x-slot>
        </x-rds.empty-state>

    @endif

</x-rds.resource.index>

@endsection