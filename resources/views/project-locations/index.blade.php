@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-5" x-data="{ tab: 'floors', activeUnit: null, activeRoom: null }">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Project Structure Manager</h1>
            <p class="text-sm text-gray-500">Block → Floor → Unit → Room → Sub-space</p>
        </div>

        @if($selectedProject)
            <div class="flex gap-2">
                <a href="{{ route('project-locations.wizard', $selectedProject->id) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
                    Generate Structure
                </a>

                <a href="{{ route('projects.edit', $selectedProject->id) }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
                    Back to Project
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded-md mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded-md mb-4 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="GET"
          action="{{ route('project-locations.index') }}"
          class="bg-white border rounded-lg shadow-sm p-4 mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Select Project</label>

        <select name="project_id"
                onchange="this.form.submit()"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
            <option value="">Select Project</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                    {{ $project->project_code }} — {{ $project->project_name }}
                </option>
            @endforeach
        </select>
    </form>

    @if($selectedProject)

        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white rounded-lg p-5 mb-4 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold">{{ $selectedProject->project_name }}</h2>
                    <p class="text-sm text-blue-100">{{ $selectedProject->project_code }}</p>
                </div>

                <div class="grid grid-cols-5 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold">{{ $blocks->count() }}</div>
                        <div class="text-xs text-blue-100">Blocks</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $floors->count() }}</div>
                        <div class="text-xs text-blue-100">Floors</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $units->count() }}</div>
                        <div class="text-xs text-blue-100">Units</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $rooms->count() }}</div>
                        <div class="text-xs text-blue-100">Rooms</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $subspaces->count() }}</div>
                        <div class="text-xs text-blue-100">Elements</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden mb-4">
            <div class="bg-gray-100 px-4 pt-3 border-b">
                <div class="flex gap-2 flex-wrap">
                    <button type="button"
                            @click="tab='floors'"
                            :class="tab === 'floors' ? 'bg-white text-blue-700 border-blue-500' : 'bg-gray-200 text-gray-600'"
                            class="px-4 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold">
                        Floor Cards
                    </button>

                    <button type="button"
                            @click="tab='manual'"
                            :class="tab === 'manual' ? 'bg-white text-blue-700 border-blue-500' : 'bg-gray-200 text-gray-600'"
                            class="px-4 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold">
                        Manual Add
                    </button>
                </div>
            </div>

            <div class="p-4">

                <div x-show="tab === 'floors'">

                    @forelse($blocks as $block)

                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ $block->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $block->type }}</p>
                                </div>

                                <a href="{{ route('project-locations.blocks.edit', $block) }}"
                                   class="bg-yellow-500 text-white px-3 py-1.5 rounded text-sm">
                                    Edit Block
                                </a>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                                @foreach($floors->where('project_block_id', $block->id) as $floor)

                                    @php
                                        $floorUnits = $units->where('project_floor_id', $floor->id);
                                        $floorRooms = $rooms->where('project_floor_id', $floor->id);
                                        $floorSubspaces = $subspaces->where('project_floor_id', $floor->id);

                                        $usage = $floor->usage_type ?? 'Residential Flats';

                                        $usageClasses = [
                                            'Residential Flats' => 'bg-blue-50 border-blue-200 text-blue-800',
                                            'Parking' => 'bg-gray-50 border-gray-300 text-gray-800',
                                            'Shops / Commercial' => 'bg-green-50 border-green-200 text-green-800',
                                            'Amenities' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                                            'Service Area' => 'bg-orange-50 border-orange-200 text-orange-800',
                                            'Mixed Use' => 'bg-purple-50 border-purple-200 text-purple-800',
                                        ];

                                        $usageClass = $usageClasses[$usage] ?? 'bg-gray-50 border-gray-200 text-gray-800';
                                    @endphp

                                    <div class="border rounded-lg overflow-hidden shadow-sm {{ $usageClass }}">
                                        <div class="p-4 border-b bg-white/60">
                                            <div class="flex justify-between gap-3">
                                                <div>
                                                    <h4 class="text-lg font-bold">{{ $floor->name }}</h4>
                                                    <p class="text-xs mt-1">{{ $usage }}</p>
                                                </div>

                                                <a href="{{ route('project-locations.floors.edit', $floor) }}"
                                                   class="bg-yellow-500 text-white px-2 py-1 rounded text-xs h-fit">
                                                    Edit
                                                </a>
                                            </div>

                                            <form method="POST"
                                                  action="{{ route('project-locations.floors.convert-usage', $floor->id) }}"
                                                  class="mt-3 flex gap-2">
                                                @csrf
                                                @method('PATCH')

                                                <select name="usage_type"
                                                        class="w-full border rounded px-2 py-1.5 text-sm bg-white">
                                                    @foreach([
                                                        'Residential Flats',
                                                        'Parking',
                                                        'Shops / Commercial',
                                                        'Amenities',
                                                        'Service Area',
                                                        'Mixed Use'
                                                    ] as $option)
                                                        <option value="{{ $option }}" {{ $usage == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button type="submit"
                                                        onclick="return confirm('Change floor usage? If not Residential Flats, active units/rooms/subspaces on this floor will be hidden.')"
                                                        class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm">
                                                    Apply
                                                </button>
                                            </form>
                                        </div>

                                        <div class="p-4 bg-white">
                                            <div class="grid grid-cols-3 gap-2 mb-3 text-center">
                                                <div class="bg-gray-50 rounded p-2">
                                                    <div class="font-bold text-gray-900">{{ $floorUnits->count() }}</div>
                                                    <div class="text-xs text-gray-500">Units</div>
                                                </div>
                                                <div class="bg-gray-50 rounded p-2">
                                                    <div class="font-bold text-gray-900">{{ $floorRooms->count() }}</div>
                                                    <div class="text-xs text-gray-500">Rooms</div>
                                                </div>
                                                <div class="bg-gray-50 rounded p-2">
                                                    <div class="font-bold text-gray-900">{{ $floorSubspaces->count() }}</div>
                                                    <div class="text-xs text-gray-500">Elements</div>
                                                </div>
                                            </div>

                                            @if($floorUnits->count())
                                                <div class="space-y-2">
                                                    @foreach($floorUnits as $unit)
                                                        @php
                                                            $unitRooms = $rooms->where('project_unit_id', $unit->id);
                                                            $unitSubspaces = $subspaces->where('project_unit_id', $unit->id);
                                                        @endphp

                                                        <div class="border rounded-md">
                                                            <button type="button"
                                                                    @click="activeUnit === {{ $unit->id }} ? activeUnit = null : activeUnit = {{ $unit->id }}; activeRoom = null"
                                                                    class="w-full flex justify-between items-center px-3 py-2 hover:bg-gray-50 text-left">
                                                                <div>
                                                                    <div class="font-semibold text-sm">{{ $unit->name }}</div>
                                                                    <div class="text-xs text-gray-500">
                                                                        {{ $unitRooms->count() }} rooms · {{ $unitSubspaces->count() }} elements
                                                                    </div>
                                                                </div>
                                                                <span class="text-xs text-blue-600">Open</span>
                                                            </button>

                                                            <div x-show="activeUnit === {{ $unit->id }}" style="display:none;" class="border-t p-3 bg-gray-50">
                                                                <div class="flex justify-between items-center mb-2">
                                                                    <h5 class="font-bold text-sm">{{ $unit->name }} Rooms</h5>
                                                                    <a href="{{ route('project-locations.units.edit', $unit) }}"
                                                                       class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                                                                        Edit Unit
                                                                    </a>
                                                                </div>

                                                                <div class="grid grid-cols-1 gap-2">
                                                                    @foreach($unitRooms as $room)
                                                                        @php
                                                                            $roomSubspaces = $subspaces->where('project_room_id', $room->id);
                                                                        @endphp

                                                                        <div class="bg-white border rounded">
                                                                            <button type="button"
                                                                                    @click="activeRoom === {{ $room->id }} ? activeRoom = null : activeRoom = {{ $room->id }}"
                                                                                    class="w-full px-3 py-2 flex justify-between text-left hover:bg-blue-50">
                                                                                <div>
                                                                                    <div class="font-semibold text-sm">{{ $room->name }}</div>
                                                                                    <div class="text-xs text-gray-500">{{ $room->room_type }}</div>
                                                                                </div>
                                                                                <div class="text-xs text-gray-500">
                                                                                    {{ $roomSubspaces->count() }} elements
                                                                                </div>
                                                                            </button>

                                                                            <div x-show="activeRoom === {{ $room->id }}" style="display:none;" class="border-t p-3">
                                                                                <div class="flex justify-between items-center mb-2">
                                                                                    <span class="text-sm font-bold">Sub-spaces</span>
                                                                                    <a href="{{ route('project-locations.rooms.edit', $room) }}"
                                                                                       class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                                                                                        Edit Room
                                                                                    </a>
                                                                                </div>

                                                                                <div class="flex flex-wrap gap-2">
                                                                                    @foreach($roomSubspaces as $subspace)
                                                                                        <a href="{{ route('project-locations.subspaces.edit', $subspace) }}"
                                                                                           class="bg-gray-100 hover:bg-blue-50 border rounded px-2 py-1 text-xs">
                                                                                            {{ $subspace->name }}
                                                                                        </a>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-500 bg-gray-50 border rounded p-3">
                                                    No active units on this floor.
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                @endforeach

                            </div>
                        </div>

                    @empty
                        <div class="text-center text-gray-500 p-10">
                            No blocks found. Generate project structure first.
                        </div>
                    @endforelse

                </div>

                <div x-show="tab === 'manual'" style="display:none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <details class="border rounded-lg p-4 bg-gray-50" open>
                            <summary class="cursor-pointer font-bold">Add Block / Building</summary>
                            <form method="POST" action="{{ route('project-locations.blocks.store') }}" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

                                <select name="name" class="w-full border rounded px-3 py-2 text-sm" required>
                                    <option value="">Select Block Master</option>
                                    @foreach($blockMasters as $master)
                                        <option value="{{ $master->name }}">{{ $master->name }}</option>
                                    @endforeach
                                </select>

                                <input type="text" name="code" placeholder="Code" class="w-full border rounded px-3 py-2 text-sm">

                                <select name="type" class="w-full border rounded px-3 py-2 text-sm" required>
                                    <option value="Building">Building</option>
                                    <option value="Block">Block</option>
                                    <option value="Tower">Tower</option>
                                    <option value="Villa">Villa</option>
                                    <option value="External Area">External Area</option>
                                    <option value="Not Applicable">Not Applicable</option>
                                </select>

                                <input type="text" name="remarks" placeholder="Remarks" class="w-full border rounded px-3 py-2 text-sm">

                                <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm">Add Block</button>
                            </form>
                        </details>

                        <details class="border rounded-lg p-4 bg-gray-50">
                            <summary class="cursor-pointer font-bold">Add Floor</summary>
                            <form method="POST" action="{{ route('project-locations.floors.store') }}" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

                                <select name="project_block_id" class="w-full border rounded px-3 py-2 text-sm" required>
                                    <option value="">Select Block</option>
                                    @foreach($blocks as $block)
                                        <option value="{{ $block->id }}">{{ $block->name }}</option>
                                    @endforeach
                                </select>

                                <select name="name" class="w-full border rounded px-3 py-2 text-sm" required>
                                    <option value="">Select Floor Master</option>
                                    @foreach($floorMasters as $master)
                                        <option value="{{ $master->name }}">{{ $master->name }}</option>
                                    @endforeach
                                </select>

                                <input type="number" name="sequence" placeholder="Sequence" class="w-full border rounded px-3 py-2 text-sm">

                                <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm">Add Floor</button>
                            </form>
                        </details>

                    </div>
                </div>

            </div>
        </div>

    @endif

</div>

@endsection