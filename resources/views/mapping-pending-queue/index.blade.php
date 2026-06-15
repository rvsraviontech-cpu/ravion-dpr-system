@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Mapping Pending Queue
</h1>

@if(session('success'))
<div class="bg-green-100 text-green-800 p-4 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded shadow overflow-hidden">

    <table class="w-full">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-4 text-left">#</th>
                <th class="p-4 text-left">Project</th>
                <th class="p-4 text-left">Engineer</th>
                <th class="p-4 text-left">Activity</th>
                <th class="p-4 text-left">Quantity</th>
                <th class="p-4 text-left">Location</th>
                <th class="p-4 text-left">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($pendingWorkItems as $index => $item)

            <tr class="border-t">

                <td class="p-4">
                    {{ $pendingWorkItems->firstItem() + $index }}
                </td>

                <td class="p-4">
                    {{ $item->dpr->project->project_name ?? '-' }}
                </td>

                <td class="p-4">
                    {{ $item->dpr->user->name ?? '-' }}
                </td>

                <td class="p-4">
                    {{ $item->activity->activity_name ?? '-' }}
                </td>

                <td class="p-4">
                    {{ number_format($item->quantity_completed, 2) }}
                </td>

                <td class="p-4">
                    {{ $item->block->name ?? $item->block->block_name ?? '-' }}

                    @if($item->floor)
                        / {{ $item->floor->name ?? $item->floor->floor_name }}
                    @endif

                    @if($item->unit)
                        / {{ $item->unit->name ?? $item->unit->unit_name }}
                    @endif

                    @if($item->room)
                        / {{ $item->room->name ?? $item->room->room_name }}
                    @endif

                    @if($item->subspace)
                        / {{ $item->subspace->name ?? $item->subspace->subspace_name }}
                    @endif
                </td>

                <td class="p-4">
                    <a href="{{ route('mapping-pending-queue.edit', $item->id) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded">
                        Map Activity
                    </a>
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="7" class="text-center p-8 text-gray-500">
                    No pending mappings found.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

<div class="mt-4">
    {{ $pendingWorkItems->links() }}
</div>

@endsection