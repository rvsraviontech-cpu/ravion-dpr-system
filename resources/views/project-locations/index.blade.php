@extends('layouts.app')

@section('content')
@php
    $canManage = auth()->user()?->hasPermission('location_masters.manage') ?? false;
    $activeBlocks = $blocks->where('is_active', true);
    $activeFloors = $floors->where('is_active', true);
    $activeUnits = $units->where('is_active', true);
    $activeRooms = $rooms->where('is_active', true);
    $activeSubspaces = $subspaces->where('is_active', true);
@endphp

<div class="max-w-7xl mx-auto px-4 py-5" x-data="{ addBlock:false, addFloor:false, openFloor:null, addUnit:null, addCommon:null }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-5">
        <div>
            <div class="text-xs font-semibold tracking-wider uppercase text-slate-500">Project Setup</div>
            <h1 class="text-2xl font-bold text-slate-900">Project Structure</h1>
            <p class="text-sm text-slate-500 mt-1">Flexible building, floor, functional-unit and common-space hierarchy.</p>
        </div>
        @if($selectedProject)
            <div class="flex flex-wrap gap-2">
                @if($canManage)
                    <button type="button" @click="addBlock = !addBlock" class="px-3 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold">+ Add Block</button>
                    <button type="button" @click="addFloor = !addFloor" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold">+ Add Floor</button>
                @endif
                <a href="{{ route('projects.edit', $selectedProject->id) }}" class="px-3 py-2 rounded-lg border bg-white text-slate-700 text-sm font-semibold">Project Master</a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('project-locations.index') }}" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm mb-4">
        <div class="grid md:grid-cols-[1fr_auto] gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Project</label>
                <select name="project_id" onchange="this.form.submit()" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Select a project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected((int)$selectedProjectId === (int)$project->id)>{{ $project->project_code }} — {{ $project->project_name }}</option>
                    @endforeach
                </select>
            </div>
            @if($selectedProject)
                <div class="text-xs text-slate-500 pb-2">{{ $selectedProject->status }}</div>
            @endif
        </div>
    </form>

    @if(!$selectedProject)
        <div class="border border-dashed border-slate-300 rounded-xl bg-slate-50 p-12 text-center text-slate-500">Select a project to open its structure workspace.</div>
    @else
        <div class="rounded-xl bg-slate-900 text-white p-5 mb-4 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-300">{{ $selectedProject->project_code }}</div>
                    <h2 class="text-xl font-bold mt-1">{{ $selectedProject->project_name }}</h2>
                    <div class="flex flex-wrap gap-2 mt-2 text-xs text-slate-300">
                        @if($selectedProject->project_type)<span>{{ $selectedProject->project_type }}</span>@endif
                        @if($selectedProject->structure_type)<span>• {{ $selectedProject->structure_type }}</span>@endif
                        @if($selectedProject->location)<span>• {{ $selectedProject->location }}</span>@endif
                    </div>
                </div>
                <div class="grid grid-cols-5 gap-5 text-center min-w-[420px]">
                    <div><div class="text-xl font-bold">{{ $activeBlocks->count() }}</div><div class="text-[11px] text-slate-300">Blocks</div></div>
                    <div><div class="text-xl font-bold">{{ $activeFloors->count() }}</div><div class="text-[11px] text-slate-300">Floors</div></div>
                    <div><div class="text-xl font-bold">{{ $activeUnits->count() }}</div><div class="text-[11px] text-slate-300">Units</div></div>
                    <div><div class="text-xl font-bold">{{ $activeRooms->count() }}</div><div class="text-[11px] text-slate-300">Spaces</div></div>
                    <div><div class="text-xl font-bold">{{ $activeSubspaces->count() }}</div><div class="text-[11px] text-slate-300">Elements</div></div>
                </div>
            </div>
        </div>

        @if($canManage)
            <div x-show="addBlock" x-cloak class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
                <div class="font-bold text-slate-900 mb-3">Add Block / Building</div>
                <form method="POST" action="{{ route('project-locations.blocks.store') }}" class="grid md:grid-cols-5 gap-3">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                    <select name="spatial_block_type_id" required class="rounded-lg border-slate-300 text-sm">
                        <option value="">Block / Building Type</option>
                        @foreach($spatialBlockTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                    </select>
                    <input name="identifier" placeholder="Identifier e.g. A / 1 / Main" class="rounded-lg border-slate-300 text-sm">
                    <input name="code" placeholder="Code (optional)" class="rounded-lg border-slate-300 text-sm">
                    <input name="remarks" placeholder="Remarks" class="rounded-lg border-slate-300 text-sm">
                    <button class="rounded-lg bg-slate-900 text-white text-sm font-semibold">Create Block</button>
                </form>
            </div>

            <div x-show="addFloor" x-cloak class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
                <div class="font-bold text-slate-900 mb-3">Add Floor / Level</div>
                <form method="POST" action="{{ route('project-locations.floors.store') }}" class="grid md:grid-cols-6 gap-3">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                    <select name="project_block_id" required class="rounded-lg border-slate-300 text-sm">
                        <option value="">Block / Building</option>
                        @foreach($activeBlocks as $block)<option value="{{ $block->id }}">{{ $block->name }}</option>@endforeach
                    </select>
                    <select name="spatial_level_type_id" required class="rounded-lg border-slate-300 text-sm">
                        <option value="">Level / Floor Type</option>
                        @foreach($spatialLevelTypes as $level)<option value="{{ $level->id }}">{{ $level->name }}</option>@endforeach
                    </select>
                    <input name="level_identifier" placeholder="No. / identifier e.g. 1" class="rounded-lg border-slate-300 text-sm">
                    <select name="spatial_floor_usage_id" required class="rounded-lg border-slate-300 text-sm">
                        <option value="">Floor Usage</option>
                        @foreach($spatialFloorUsages as $usage)<option value="{{ $usage->id }}">{{ $usage->name }}</option>@endforeach
                    </select>
                    <input name="sequence" type="number" value="0" required placeholder="Sequence" class="rounded-lg border-slate-300 text-sm">
                    <input name="remarks" placeholder="Remarks (optional)" class="rounded-lg border-slate-300 text-sm">
                    <button class="md:col-span-6 rounded-lg bg-blue-600 py-2 text-white text-sm font-semibold">Create Master-Driven Floor</button>
                </form>
                <p class="text-xs text-slate-500 mt-2">There is no cellar/basement count limit in the flexible designer. Sequence controls building order.</p>
            </div>
        @endif

        <div class="space-y-4">
            @forelse($activeBlocks as $block)
                @php $blockFloors = $activeFloors->where('project_block_id', $block->id)->sortBy('sequence'); @endphp
                <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2"><h3 class="font-bold text-slate-900">{{ $block->name }}</h3><span class="text-[11px] rounded-full bg-white border px-2 py-0.5 text-slate-500">{{ $block->type }}</span></div>
                            <div class="text-xs text-slate-500 mt-1">{{ $blockFloors->count() }} active floor(s)</div>
                        </div>
                        @if($canManage)<a href="{{ route('project-locations.blocks.edit', $block) }}" class="text-xs font-semibold text-blue-700">Edit Block</a>@endif
                    </div>

                    <div class="p-4 space-y-3">
                        @forelse($blockFloors as $floor)
                            @php
                                $floorUnits = $activeUnits->where('project_floor_id', $floor->id);
                                $floorRooms = $activeRooms->where('project_floor_id', $floor->id);
                                $commonRooms = $floorRooms->whereNull('project_unit_id');
                                $floorElements = $activeSubspaces->where('project_floor_id', $floor->id);
                            @endphp
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <button type="button" @click="openFloor === {{ $floor->id }} ? openFloor = null : openFloor = {{ $floor->id }}" class="w-full px-4 py-3 flex items-center justify-between gap-4 text-left hover:bg-slate-50">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">{{ $floor->sequence }}</div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 truncate">{{ $floor->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $floor->usage_type ?: 'Other' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-slate-500">
                                        <span>{{ $floorUnits->count() }} units</span><span>{{ $commonRooms->count() }} common spaces</span><span>{{ $floorRooms->count() }} total spaces</span><span class="text-blue-700 font-semibold">Open</span>
                                    </div>
                                </button>

                                <div x-show="openFloor === {{ $floor->id }}" x-cloak class="border-t bg-slate-50/60 p-4">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full bg-blue-50 border border-blue-200 text-blue-700 px-2.5 py-1 text-xs font-semibold">{{ $floor->usage_type ?: 'Other' }}</span>
                                        </div>
                                        @if($canManage)
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" @click="addUnit = addUnit === {{ $floor->id }} ? null : {{ $floor->id }}" class="text-xs font-semibold border bg-white rounded-lg px-3 py-1.5">+ Functional Unit</button>
                                                <button type="button" @click="addCommon = addCommon === {{ $floor->id }} ? null : {{ $floor->id }}" class="text-xs font-semibold border bg-white rounded-lg px-3 py-1.5">+ Common Space</button>
                                                <a href="{{ route('project-locations.floors.edit', $floor) }}" class="text-xs font-semibold border bg-white rounded-lg px-3 py-1.5">Edit Floor</a>
                                            </div>
                                        @endif
                                    </div>

                                    @if($canManage)
                                        <div x-show="addUnit === {{ $floor->id }}" x-cloak class="mb-3 bg-white border rounded-lg p-3">
                                            <form method="POST" action="{{ route('project-locations.units.store') }}" class="grid md:grid-cols-4 gap-2">
                                                @csrf
                                                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}"><input type="hidden" name="project_block_id" value="{{ $block->id }}"><input type="hidden" name="project_floor_id" value="{{ $floor->id }}">
                                                <select name="spatial_functional_unit_type_id" required class="rounded-lg border-slate-300 text-sm">
                                                    <option value="">Functional Unit Type</option>
                                                    @foreach($functionalUnitTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                                                </select>
                                                <input name="identifier" required placeholder="Identifier e.g. 101 / A / ICU-1" class="rounded-lg border-slate-300 text-sm">
                                                <input name="remarks" placeholder="Remarks (optional)" class="rounded-lg border-slate-300 text-sm">
                                                <button class="rounded-lg bg-slate-900 text-white text-sm font-semibold">Create Unit</button>
                                            </form>
                                        </div>

                                        <div x-show="addCommon === {{ $floor->id }}" x-cloak class="mb-3 bg-white border rounded-lg p-3">
                                            <form method="POST" action="{{ route('project-locations.rooms.store') }}" class="grid md:grid-cols-4 gap-2">
                                                @csrf
                                                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}"><input type="hidden" name="project_block_id" value="{{ $block->id }}"><input type="hidden" name="project_floor_id" value="{{ $floor->id }}"><input type="hidden" name="project_unit_id" value="">
                                                <div x-data="{
                                                    category: '',
                                                    type: '',
                                                    types: @js($spaceTypes->map(fn($s) => ['id'=>$s->id,'name'=>$s->name,'category_id'=>$s->spatial_space_category_id])->values()),
                                                    subtypes: @js($spaceSubtypes->map(fn($s) => ['id'=>$s->id,'name'=>$s->name,'type_id'=>$s->spatial_space_type_id])->values())
                                                }" class="md:col-span-4 grid md:grid-cols-5 gap-2">
                                                    <select x-model="category" @change="type=''" class="rounded-lg border-slate-300 text-sm">
                                                        <option value="">Space Category</option>
                                                        @foreach($spaceCategories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
                                                    </select>
                                                    <select x-model="type" name="spatial_space_type_id" required class="rounded-lg border-slate-300 text-sm">
                                                        <option value="">Space Type</option>
                                                        <template x-for="item in types.filter(i => !category || String(i.category_id) === String(category))" :key="item.id">
                                                            <option :value="item.id" x-text="item.name"></option>
                                                        </template>
                                                    </select>
                                                    <select name="spatial_space_subtype_id" class="rounded-lg border-slate-300 text-sm">
                                                        <option value="">Subtype (optional)</option>
                                                        <template x-for="item in subtypes.filter(i => type && String(i.type_id) === String(type))" :key="item.id">
                                                            <option :value="item.id" x-text="item.name"></option>
                                                        </template>
                                                    </select>
                                                    <input name="identifier" placeholder="Identifier (optional)" class="rounded-lg border-slate-300 text-sm">
                                                    <input name="remarks" placeholder="Remarks (optional)" class="rounded-lg border-slate-300 text-sm">
                                                </div>
                                                <button class="md:col-span-4 rounded-lg bg-blue-600 py-2 text-white text-sm font-semibold">Create Common Space</button>
                                            </form>
                                            <p class="text-[11px] text-slate-500 mt-2">Saved directly under this floor. No dummy unit is created.</p>
                                        </div>
                                    @endif

                                    <div class="grid lg:grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Functional Units</div>
                                            <div class="space-y-2">
                                                @forelse($floorUnits as $unit)
                                                    @php $unitRooms = $floorRooms->where('project_unit_id', $unit->id); @endphp
                                                    <div class="bg-white border rounded-lg p-3 flex justify-between gap-3">
                                                        <div><div class="font-semibold text-sm text-slate-900">{{ $unit->name }}</div><div class="text-xs text-slate-500">{{ $unit->type ?: 'Custom unit' }} · {{ $unitRooms->count() }} spaces</div></div>
                                                        @if($canManage)<a href="{{ route('project-locations.units.edit', $unit) }}" class="text-xs font-semibold text-blue-700">Edit</a>@endif
                                                    </div>
                                                @empty
                                                    <div class="bg-white border border-dashed rounded-lg p-4 text-xs text-slate-500">No functional units on this floor. This is valid for parking, service and common-only floors.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Floor Common / Service Spaces</div>
                                            <div class="space-y-2">
                                                @forelse($commonRooms as $room)
                                                    <div class="bg-white border rounded-lg p-3 flex justify-between gap-3">
                                                        <div><div class="font-semibold text-sm text-slate-900">{{ $room->name }}</div><div class="text-xs text-slate-500">{{ $room->room_type ?: 'Common space' }}</div></div>
                                                        @if($canManage)<a href="{{ route('project-locations.rooms.edit', $room) }}" class="text-xs font-semibold text-blue-700">Edit</a>@endif
                                                    </div>
                                                @empty
                                                    <div class="bg-white border border-dashed rounded-lg p-4 text-xs text-slate-500">No direct floor spaces yet. Add parking, security, electrical, washroom, pump room, lobby or any custom space here.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    @if($floorElements->count())
                                        <div class="mt-3 text-[11px] text-slate-400">Existing lower-level elements preserved: {{ $floorElements->count() }}. Detailed room/geometry/wall editing follows in the next designer step.</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="border border-dashed rounded-xl p-8 text-center text-sm text-slate-500">No active floors in this block.</div>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="border border-dashed border-slate-300 rounded-xl bg-slate-50 p-10 text-center">
                    <div class="font-semibold text-slate-700">No active blocks/buildings yet</div>
                    <div class="text-sm text-slate-500 mt-1">Create the first building/block, then add floors independently.</div>
                </div>
            @endforelse
        </div>

        @if($blocks->where('is_active', false)->count() || $floors->where('is_active', false)->count())
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">Archived/inactive structure records are preserved in the database and are not deleted. They can be reactivated from their edit screens.</div>
        @endif
    @endif
</div>
@endsection
