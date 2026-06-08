@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

<div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl shadow-lg p-6 mb-6 text-white">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">Project Location Setup</h1>
            <p class="mt-2 text-blue-100 text-sm">
                Setup DPR hierarchy: Block → Floor → Unit → Room → Sub-Space
            </p>
        </div>

        <div class="bg-white/10 rounded-xl px-4 py-3 text-sm">
            <div class="text-blue-100">Ravion DPR</div>
            <div class="font-semibold">Location Master</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl mb-4 shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl mb-4 shadow-sm">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" action="{{ route('project-locations.index') }}">
        <label class="block font-semibold mb-2">Select Project</label>

        <select name="project_id"
                class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                onchange="this.form.submit()">
            <option value="">Select Project</option>

            @foreach($projects as $project)
                <option value="{{ $project->id }}"
                    {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                    {{ $project->project_name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

@if($selectedProjectId)

<details class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden" open>
    <summary class="cursor-pointer px-6 py-4 font-bold text-lg bg-blue-50 text-blue-800 border-b border-blue-100">
        Add Block / Building
    </summary>

    <div class="p-6">
        <form method="POST" action="{{ route('project-locations.blocks.store') }}">
            @csrf

            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Block Master</label>
                    <select name="name" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Block Master</option>
                        @foreach($blockMasters as $master)
                            <option value="{{ $master->name }}">
                                {{ $master->name }}
                                @if($master->type)
                                    ({{ $master->type }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Code</label>
                    <input type="text" name="code" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Type</label>
                    <select name="type" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="Building">Building</option>
                        <option value="Block">Block</option>
                        <option value="Tower">Tower</option>
                        <option value="Villa">Villa</option>
                        <option value="External Area">External Area</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Remarks</label>
                    <input type="text" name="remarks" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl mt-4 shadow-sm transition">
                Add Block
            </button>
        </form>
    </div>
</details>

<details class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
    <summary class="cursor-pointer px-6 py-4 font-bold text-lg bg-blue-50 text-blue-800 border-b border-blue-100">
        Add Floor
    </summary>

    <div class="p-6">
        <form method="POST" action="{{ route('project-locations.floors.store') }}">
            @csrf

            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Block / Building</label>
                    <select name="project_block_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Block</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}">{{ $block->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Floor Master</label>
                    <select name="name" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Floor Master</option>
                        @foreach($floorMasters as $master)
                            <option value="{{ $master->name }}">{{ $master->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Sequence</label>
                    <select name="sequence" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        @foreach($floorMasters as $master)
                            <option value="{{ $master->sequence }}">
                                {{ $master->sequence }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Remarks</label>
                    <input type="text" name="remarks" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl mt-4 shadow-sm transition">
                Add Floor
            </button>
        </form>
    </div>
</details>

<details class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
    <summary class="cursor-pointer px-6 py-4 font-bold text-lg bg-blue-50 text-blue-800 border-b border-blue-100">
        Add Unit / Flat / Villa
    </summary>

    <div class="p-6">
        <form method="POST" action="{{ route('project-locations.units.store') }}">
            @csrf

            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Block / Building</label>
                    <select name="project_block_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Block</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}">{{ $block->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Floor</label>
                    <select name="project_floor_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Floor</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}">
                                {{ $floor->block?->name }} - {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Unit Master</label>
                    <select name="name" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Unit Master</option>
                        @foreach($unitMasters as $master)
                            <option value="{{ $master->name }}">
                                {{ $master->name }}
                                @if($master->type)
                                    ({{ $master->type }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Type</label>
                    <select name="type" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">Select Type</option>
                        @foreach($unitMasters as $master)
                            @if($master->type)
                                <option value="{{ $master->type }}">
                                    {{ $master->type }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Remarks</label>
                    <input type="text" name="remarks" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl mt-4 shadow-sm transition">
                Add Unit
            </button>
        </form>
    </div>
</details>

<details class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
    <summary class="cursor-pointer px-6 py-4 font-bold text-lg bg-blue-50 text-blue-800 border-b border-blue-100">
        Add Room / Space
    </summary>

    <div class="p-6">
        <form method="POST" action="{{ route('project-locations.rooms.store') }}">
            @csrf

            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Block</label>
                    <select name="project_block_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Block</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}">{{ $block->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Floor</label>
                    <select name="project_floor_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Floor</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}">
                                {{ $floor->block?->name }} - {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Unit</label>
                    <select name="project_unit_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Room Master</label>
                    <select name="name" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Room Master</option>
                        @foreach($roomMasters as $master)
                            <option value="{{ $master->name }}">
                                {{ $master->name }}
                                @if($master->room_type)
                                    ({{ $master->room_type }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Room Type</label>
                    <select name="room_type" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">Select Room Type</option>
                        @foreach($roomMasters as $master)
                            @if($master->room_type)
                                <option value="{{ $master->room_type }}">
                                    {{ $master->room_type }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Remarks</label>
                    <input type="text" name="remarks" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl mt-4 shadow-sm transition">
                Add Room
            </button>
        </form>
    </div>
</details>

<details class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
    <summary class="cursor-pointer px-6 py-4 font-bold text-lg bg-blue-50 text-blue-800 border-b border-blue-100">
        Add Sub-space / Element
    </summary>

    <div class="p-6">
        <form method="POST" action="{{ route('project-locations.subspaces.store') }}">
            @csrf

            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Block</label>
                    <select name="project_block_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Block</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}">{{ $block->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Floor</label>
                    <select name="project_floor_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Floor</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}">
                                {{ $floor->block?->name }} - {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Unit</label>
                    <select name="project_unit_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Room</label>
                    <select name="project_room_id" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Room</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">
                                {{ $room->unit?->name }} - {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Sub-space Master</label>
                    <select name="name" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" required>
                        <option value="">Select Sub-space Master</option>
                        @foreach($subspaceMasters as $master)
                            <option value="{{ $master->name }}">
                                {{ $master->name }}
                                @if($master->type)
                                    ({{ $master->type }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Type</label>
                    <select name="type" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">Select Type</option>
                        @foreach($subspaceMasters as $master)
                            @if($master->type)
                                <option value="{{ $master->type }}">
                                    {{ $master->type }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-6">
                    <label class="block font-semibold mb-1">Remarks</label>
                    <input type="text" name="remarks" class="border border-gray-300 p-3 rounded-xl w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl mt-4 shadow-sm transition">
                Add Sub-space
            </button>
        </form>
    </div>
</details>

<h2 class="text-2xl font-bold mb-4 mt-8 text-gray-900">
    Existing Project Locations
</h2>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <h2 class="text-xl font-bold mb-4">Blocks / Buildings</h2>

        <table class="w-full text-sm min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blocks as $block)
                    <tr class="border-t hover:bg-blue-50 transition">
                        <td class="p-3">{{ $block->name }}</td>
                        <td class="p-3">{{ $block->type }}</td>
                        <td class="p-3">{{ $block->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-3 flex flex-wrap gap-3">
                            <a href="{{ route('project-locations.blocks.edit', $block) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">Edit</a>

                            <form method="POST"
                                  action="{{ route('project-locations.blocks.toggle-status', $block) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="{{ $block->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    {{ $block->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-gray-500">No blocks added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <h2 class="text-xl font-bold mb-4">Floors</h2>

        <table class="w-full text-sm min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Block</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Floor</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($floors as $floor)
                    <tr class="border-t hover:bg-blue-50 transition">
                        <td class="p-3">{{ $floor->block?->name ?? '-' }}</td>
                        <td class="p-3">{{ $floor->name }}</td>
                        <td class="p-3">{{ $floor->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-3 flex flex-wrap gap-3">
                            <a href="{{ route('project-locations.floors.edit', $floor) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">Edit</a>

                            <form method="POST"
                                  action="{{ route('project-locations.floors.toggle-status', $floor) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="{{ $floor->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    {{ $floor->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-gray-500">No floors added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <h2 class="text-xl font-bold mb-4">Units</h2>

        <table class="w-full text-sm min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Floor</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr class="border-t hover:bg-blue-50 transition">
                        <td class="p-3">{{ $unit->floor?->name ?? '-' }}</td>
                        <td class="p-3">{{ $unit->name }}</td>
                        <td class="p-3">{{ $unit->type ?? '-' }}</td>
                        <td class="p-3 flex flex-wrap gap-3">
                            <a href="{{ route('project-locations.units.edit', $unit) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">Edit</a>

                            <form method="POST"
                                  action="{{ route('project-locations.units.toggle-status', $unit) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="{{ $unit->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    {{ $unit->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-gray-500">No units added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <h2 class="text-xl font-bold mb-4">Rooms / Spaces</h2>

        <table class="w-full text-sm min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr class="border-t hover:bg-blue-50 transition">
                        <td class="p-3">{{ $room->unit?->name ?? '-' }}</td>
                        <td class="p-3">{{ $room->name }}</td>
                        <td class="p-3">{{ $room->room_type ?? '-' }}</td>
                        <td class="p-3 flex flex-wrap gap-3">
                            <a href="{{ route('project-locations.rooms.edit', $room) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">Edit</a>

                            <form method="POST"
                                  action="{{ route('project-locations.rooms.toggle-status', $room) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="{{ $room->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    {{ $room->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-gray-500">No rooms added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto md:col-span-2">
        <h2 class="text-xl font-bold mb-4">Sub-spaces / Elements</h2>

        <table class="w-full text-sm min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sub-space</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subspaces as $subspace)
                    <tr class="border-t hover:bg-blue-50 transition">
                        <td class="p-3">{{ $subspace->room?->name ?? '-' }}</td>
                        <td class="p-3">{{ $subspace->name }}</td>
                        <td class="p-3">{{ $subspace->type ?? '-' }}</td>
                        <td class="p-3 flex flex-wrap gap-3">
                            <a href="{{ route('project-locations.subspaces.edit', $subspace) }}"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">Edit</a>

                            <form method="POST"
                                  action="{{ route('project-locations.subspaces.toggle-status', $subspace) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="{{ $subspace->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    {{ $subspace->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-3 text-gray-500">No sub-spaces added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endif

</div>

@endsection