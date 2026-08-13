@extends('layouts.app')

@section('content')
<x-rds.page-header title="Assign Labour Groups" subtitle="Assign each labour profile to an operational Labour Group used by attendance and wage reports.">
    <x-slot:actions>
        <x-rds.button href="{{ route('labour-groups.index') }}" variant="secondary">Labour Groups</x-rds.button>
    </x-slot:actions>
</x-rds.page-header>
<x-rds.alert />

<x-rds.card>
    <form method="GET" action="{{ route('labour-groups.assignments') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="w-full sm:max-w-md">
            <x-rds.select name="project_id" label="Project">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>{{ $project->project_name }}</option>
                @endforeach
            </x-rds.select>
        </div>
        <x-rds.button type="submit" variant="primary">Filter</x-rds.button>
    </form>
</x-rds.card>

<form method="POST" action="{{ route('labour-groups.assignments.update') }}" class="mt-6">
    @csrf @method('PUT')
    <x-rds.card :padding="false">
        <div class="max-h-[520px] overflow-auto">
            <table class="w-full min-w-[900px] table-fixed">
                <thead class="sticky top-0 z-10 bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Labour</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Designation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Labour Group</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($labours as $labour)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3"><div class="text-sm font-semibold">{{ $labour->full_name }}</div><div class="text-xs text-gray-500">{{ $labour->labour_code }}</div></td>
                            <td class="px-4 py-3 text-sm">{{ $labour->designationRole?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $labour->currentProject?->project_name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3">
                                <select name="groups[{{ $labour->id }}]" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">Un-grouped</option>
                                    @foreach($labourGroups as $group)
                                        <option value="{{ $group->id }}" @selected((string) $labour->labour_group_id === (string) $group->id)>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex justify-end border-t px-4 py-4"><x-rds.button type="submit" variant="primary">Save Group Assignments</x-rds.button></div>
    </x-rds.card>
</form>
@endsection
