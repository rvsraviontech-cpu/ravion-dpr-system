<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\SpatialBlockType;
use App\Models\SpatialLevelType;
use App\Models\SpatialFloorUsage;
use App\Models\SpatialFunctionalUnitType;
use App\Models\SpatialSpaceCategory;
use App\Models\SpatialSpaceType;
use App\Models\SpatialSpaceSubtype;
use Illuminate\Http\Request;
use App\Models\LocationBlockMaster;
use App\Models\LocationFloorMaster;
use App\Models\LocationUnitMaster;
use App\Models\LocationRoomMaster;
use App\Models\LocationSubspaceMaster;
use App\Helpers\AuditHelper;
use Illuminate\Support\Facades\DB;

class ProjectLocationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $this->accessibleProjectsQuery($user)
            ->orderBy('project_name')
            ->get(['id', 'project_code', 'project_name', 'project_type', 'structure_type', 'status']);

        $selectedProjectId = $request->integer('project_id') ?: null;
        $selectedProject = null;
        $blocks = collect();
        $floors = collect();
        $units = collect();
        $rooms = collect();
        $subspaces = collect();

        if ($selectedProjectId) {
            $selectedProject = Project::query()
                ->select(['id', 'project_code', 'project_name', 'project_type', 'structure_type', 'status', 'location'])
                ->findOrFail($selectedProjectId);

            $this->ensureProjectAccess($selectedProject, $user);

            $blocks = ProjectBlock::where('project_id', $selectedProjectId)
                ->orderByDesc('is_active')->orderBy('name')->get();

            $floors = ProjectFloor::with('block')
                ->where('project_id', $selectedProjectId)
                ->orderBy('project_block_id')->orderBy('sequence')->orderBy('name')->get();

            $units = ProjectUnit::with(['block', 'floor'])
                ->where('project_id', $selectedProjectId)
                ->orderByDesc('is_active')->orderBy('project_floor_id')->orderBy('name')->get();

            $rooms = ProjectRoom::with(['block', 'floor', 'unit'])
                ->where('project_id', $selectedProjectId)
                ->orderByDesc('is_active')->orderBy('project_floor_id')->orderBy('project_unit_id')->orderBy('name')->get();

            $subspaces = ProjectSubspace::with(['block', 'floor', 'unit', 'room'])
                ->where('project_id', $selectedProjectId)
                ->orderByDesc('is_active')->orderBy('project_room_id')->orderBy('name')->get();
        }

        $blockMasters = LocationBlockMaster::where('is_active', true)->orderBy('name')->get();
        $floorMasters = LocationFloorMaster::where('is_active', true)->orderBy('sequence')->orderBy('name')->get();
        $unitMasters = LocationUnitMaster::where('is_active', true)->orderBy('name')->get();
        $roomMasters = LocationRoomMaster::where('is_active', true)->orderBy('room_type')->orderBy('name')->get();
        $subspaceMasters = LocationSubspaceMaster::where('is_active', true)->orderBy('type')->orderBy('name')->get();

        $spatialBlockTypes = SpatialBlockType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $spatialLevelTypes = SpatialLevelType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $spatialFloorUsages = SpatialFloorUsage::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $functionalUnitTypes = SpatialFunctionalUnitType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $spaceCategories = SpatialSpaceCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $spaceTypes = SpatialSpaceType::where('is_active', true)
            ->with(['category:id,name'])
            ->orderBy('spatial_space_category_id')->orderBy('sort_order')->orderBy('name')->get();
        $spaceSubtypes = SpatialSpaceSubtype::where('is_active', true)
            ->orderBy('spatial_space_type_id')->orderBy('sort_order')->orderBy('name')->get();

        return view('project-locations.index', compact(
            'projects', 'selectedProject', 'selectedProjectId', 'blocks', 'floors', 'units',
            'rooms', 'subspaces', 'blockMasters', 'floorMasters', 'unitMasters',
            'roomMasters', 'subspaceMasters', 'spatialBlockTypes', 'spatialLevelTypes',
            'spatialFloorUsages', 'functionalUnitTypes', 'spaceCategories', 'spaceTypes',
            'spaceSubtypes'
        ));
    }

    // STORE BLOCK

    public function storeBlock(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'spatial_block_type_id' => 'required|exists:spatial_block_types,id',
            'identifier' => 'nullable|string|max:80',
            'code' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $project = Project::findOrFail($data['project_id']);
        $this->ensureProjectAccess($project, $request->user());

        $master = SpatialBlockType::whereKey($data['spatial_block_type_id'])->where('is_active', true)->firstOrFail();
        $identifier = trim((string) ($data['identifier'] ?? ''));
        $name = $this->generatedName($master->name, null, $identifier);

        $projectBlock = ProjectBlock::create([
            'project_id' => $project->id,
            'name' => $name,
            'code' => $data['code'] ?? null,
            'type' => $master->name,
            'spatial_block_type_id' => $master->id,
            'identifier' => $identifier ?: null,
            'is_active' => true,
            'remarks' => $data['remarks'] ?? null,
        ]);

        $this->auditProjectLocation('Block Created', 'ProjectBlock', $projectBlock,
            'Project block created: '.$projectBlock->name, null, $projectBlock->toArray());

        return redirect()->route('project-locations.index', ['project_id' => $project->id])
            ->with('success', 'Block / Building added successfully.');
    }

// STORE FLOOR

public function storeFloor(Request $request)
{
    $data = $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'spatial_level_type_id' => 'required|exists:spatial_level_types,id',
        'spatial_floor_usage_id' => 'required|exists:spatial_floor_usages,id',
        'level_identifier' => 'nullable|string|max:80',
        'sequence' => 'required|integer',
        'remarks' => 'nullable|string',
    ]);

    $project = Project::findOrFail($data['project_id']);
    $this->ensureProjectAccess($project, $request->user());
    ProjectBlock::whereKey($data['project_block_id'])->where('project_id', $project->id)->firstOrFail();

    $level = SpatialLevelType::whereKey($data['spatial_level_type_id'])->where('is_active', true)->firstOrFail();
    $usage = SpatialFloorUsage::whereKey($data['spatial_floor_usage_id'])->where('is_active', true)->firstOrFail();
    $identifier = trim((string) ($data['level_identifier'] ?? ''));

    if ($level->requires_number && $identifier === '') {
        return back()->withErrors(['level_identifier' => $level->name.' requires a level number / identifier.'])->withInput();
    }

    $name = $this->generatedName($level->name, $level->name_pattern, $identifier);

    $projectFloor = ProjectFloor::create([
        'project_id' => $project->id,
        'project_block_id' => $data['project_block_id'],
        'name' => $name,
        'sequence' => $data['sequence'],
        'usage_type' => $usage->name,
        'spatial_level_type_id' => $level->id,
        'spatial_floor_usage_id' => $usage->id,
        'level_identifier' => $identifier ?: null,
        'is_active' => true,
        'remarks' => $data['remarks'] ?? null,
    ]);

    $this->auditProjectLocation('Floor Created', 'ProjectFloor', $projectFloor,
        'Project floor created: '.$projectFloor->name, null, $projectFloor->toArray());

    return redirect()->route('project-locations.index', ['project_id' => $project->id])
        ->with('success', 'Floor added successfully.');
}

// STORE UNIT

public function storeUnit(Request $request)
{
    $data = $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'spatial_functional_unit_type_id' => 'required|exists:spatial_functional_unit_types,id',
        'identifier' => 'required|string|max:80',
        'remarks' => 'nullable|string',
    ]);

    $project = Project::findOrFail($data['project_id']);
    $this->ensureProjectAccess($project, $request->user());
    ProjectFloor::whereKey($data['project_floor_id'])
        ->where('project_id', $project->id)
        ->where('project_block_id', $data['project_block_id'])->firstOrFail();

    $master = SpatialFunctionalUnitType::whereKey($data['spatial_functional_unit_type_id'])
        ->where('is_active', true)->firstOrFail();

    $identifier = trim($data['identifier']);
    $name = $this->generatedName($master->name, $master->name_pattern, $identifier);

    $projectUnit = ProjectUnit::create([
        'project_id' => $project->id,
        'project_block_id' => $data['project_block_id'],
        'project_floor_id' => $data['project_floor_id'],
        'name' => $name,
        'type' => $master->name,
        'spatial_functional_unit_type_id' => $master->id,
        'identifier' => $identifier,
        'is_active' => true,
        'remarks' => $data['remarks'] ?? null,
    ]);

    $this->auditProjectLocation('Unit Created', 'ProjectUnit', $projectUnit,
        'Project unit created: '.$projectUnit->name, null, $projectUnit->toArray());

    return redirect()->route('project-locations.index', ['project_id' => $project->id])
        ->with('success', 'Functional unit added successfully.');
}

// STORE ROOM

public function storeRoom(Request $request)
{
    $data = $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'project_unit_id' => 'nullable|exists:project_units,id',
        'spatial_space_type_id' => 'required|exists:spatial_space_types,id',
        'spatial_space_subtype_id' => 'nullable|exists:spatial_space_subtypes,id',
        'identifier' => 'nullable|string|max:80',
        'remarks' => 'nullable|string',
    ]);

    $project = Project::findOrFail($data['project_id']);
    $this->ensureProjectAccess($project, $request->user());

    ProjectFloor::whereKey($data['project_floor_id'])
        ->where('project_id', $project->id)
        ->where('project_block_id', $data['project_block_id'])->firstOrFail();

    if (!empty($data['project_unit_id'])) {
        ProjectUnit::whereKey($data['project_unit_id'])
            ->where('project_id', $project->id)
            ->where('project_block_id', $data['project_block_id'])
            ->where('project_floor_id', $data['project_floor_id'])->firstOrFail();
    }

    $spaceType = SpatialSpaceType::whereKey($data['spatial_space_type_id'])->where('is_active', true)->firstOrFail();
    $subtype = null;
    if (!empty($data['spatial_space_subtype_id'])) {
        $subtype = SpatialSpaceSubtype::whereKey($data['spatial_space_subtype_id'])
            ->where('spatial_space_type_id', $spaceType->id)
            ->where('is_active', true)->firstOrFail();
    }

    $identifier = trim((string) ($data['identifier'] ?? ''));
    $baseName = $subtype?->name ?: $spaceType->name;
    $name = $identifier !== '' ? $baseName.' '.$identifier : $baseName;

    $projectRoom = ProjectRoom::create([
        'project_id' => $project->id,
        'project_block_id' => $data['project_block_id'],
        'project_floor_id' => $data['project_floor_id'],
        'project_unit_id' => $data['project_unit_id'] ?? null,
        'name' => $name,
        'room_type' => $spaceType->name,
        'spatial_space_type_id' => $spaceType->id,
        'spatial_space_subtype_id' => $subtype?->id,
        'identifier' => $identifier ?: null,
        'is_active' => true,
        'remarks' => $data['remarks'] ?? null,
    ]);

    $this->auditProjectLocation('Room Created', 'ProjectRoom', $projectRoom,
        'Project room/space created: '.$projectRoom->name, null, $projectRoom->toArray());

    return redirect()->route('project-locations.index', ['project_id' => $project->id])
        ->with('success', empty($data['project_unit_id']) ? 'Floor common space added successfully.' : 'Room / space added successfully.');
}

// STORE SUBSPACE

public function storeSubspace(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'project_unit_id' => 'required|exists:project_units,id',
        'project_room_id' => 'required|exists:project_rooms,id',
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'remarks' => 'nullable|string',
    ]);

    $project = Project::findOrFail($request->integer('project_id'));
    $this->ensureProjectAccess($project, $request->user());
    ProjectRoom::whereKey($request->integer('project_room_id'))
        ->where('project_id', $project->id)
        ->where('project_block_id', $request->integer('project_block_id'))
        ->where('project_floor_id', $request->integer('project_floor_id'))
        ->where('project_unit_id', $request->integer('project_unit_id'))->firstOrFail();

    $projectSubspace = ProjectSubspace::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'project_room_id' => $request->project_room_id,
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

    $this->auditProjectLocation(
    'Subspace Created',
    'ProjectSubspace',
    $projectSubspace,
    'Project subspace created: ' . $projectSubspace->name,
    null,
    $projectSubspace->toArray()
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $request->project_id
        ])
        ->with('success', 'Sub-space / Element added successfully.');
}

public function editBlock(ProjectBlock $projectBlock)
{
    $this->ensureProjectAccess($projectBlock->project, request()->user());
    $blockMasters = LocationBlockMaster::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('project-locations.edit-block', compact(
        'projectBlock',
        'blockMasters'
    ));
}

public function updateBlock(Request $request, ProjectBlock $projectBlock)
{
    $this->ensureProjectAccess($projectBlock->project, $request->user());
    $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:255',
        'type' => 'required|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

    $oldValues = $projectBlock->toArray();

    $projectBlock->update([
        'name' => $request->name,
        'code' => $request->code,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    $newValues = $projectBlock->fresh()->toArray();

$this->auditProjectLocation(
    'Block Updated',
    'ProjectBlock',
    $projectBlock,
    'Project block updated: ' . $projectBlock->name,
    $oldValues,
    $newValues
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectBlock->project_id
        ])
        ->with('success', 'Project block updated successfully.');
}

public function toggleBlockStatus(ProjectBlock $projectBlock)
{
    $this->ensureProjectAccess($projectBlock->project, request()->user());
    $oldValues = $projectBlock->toArray();
    $projectBlock->update([
        'is_active' => !$projectBlock->is_active,
    ]);
    $newValues = $projectBlock->fresh()->toArray();

$this->auditProjectLocation(
    $projectBlock->is_active ? 'Block Activated' : 'Block Deactivated',
    'ProjectBlock',
    $projectBlock,
    $projectBlock->is_active
        ? 'Project block activated: ' . $projectBlock->name
        : 'Project block deactivated: ' . $projectBlock->name,
    $oldValues,
    $newValues
);

    return back()->with('success', 'Project block status updated successfully.');
}

public function editFloor(ProjectFloor $projectFloor)
{
    $this->ensureProjectAccess($projectFloor->project, request()->user());
    $blocks = ProjectBlock::where('project_id', $projectFloor->project_id)
    ->orderBy('name')
    ->get();

    $floorMasters = LocationFloorMaster::where('is_active', true)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    return view('project-locations.edit-floor', compact(
        'projectFloor',
        'blocks',
        'floorMasters'
    ));
}

public function updateFloor(Request $request, ProjectFloor $projectFloor)
{
    $this->ensureProjectAccess($projectFloor->project, $request->user());
    $request->validate([
        'project_block_id' => 'required|exists:project_blocks,id',
        'name' => 'required|string|max:255',
        'sequence' => 'nullable|integer',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

     $oldValues = $projectFloor->toArray();

    $projectFloor->update([
        'project_block_id' => $request->project_block_id,
        'name' => $request->name,
        'sequence' => $request->sequence ?? 0,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

   
    $newValues = $projectFloor->fresh()->toArray();

$this->auditProjectLocation(
    'Floor Updated',
    'ProjectFloor',
    $projectFloor,
    'Project floor updated: ' . $projectFloor->name,
    $oldValues,
    $newValues
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectFloor->project_id
        ])
        ->with('success', 'Project floor updated successfully.');
}

public function toggleFloorStatus(ProjectFloor $projectFloor)
{
    $this->ensureProjectAccess($projectFloor->project, request()->user());
    $oldValues = $projectFloor->toArray();

    $projectFloor->update([
        'is_active' => !$projectFloor->is_active,
    ]);

    $newValues = $projectFloor->fresh()->toArray();

    $this->auditProjectLocation(
        $projectFloor->is_active
            ? 'Floor Activated'
            : 'Floor Deactivated',
        'ProjectFloor',
        $projectFloor,
        $projectFloor->is_active
            ? 'Project floor activated: ' . $projectFloor->name
            : 'Project floor deactivated: ' . $projectFloor->name,
        $oldValues,
        $newValues
    );

    return back()->with(
        'success',
        'Project floor status updated successfully.'
    );
}

public function editUnit(ProjectUnit $projectUnit)
{
    $this->ensureProjectAccess($projectUnit->project, request()->user());
    $projectUnit->load(['floor.block', 'rooms' => fn ($query) => $query->orderByDesc('is_active')->orderBy('name')]);
    $functionalUnitTypes = SpatialFunctionalUnitType::query()
        ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $projectUnit->spatial_functional_unit_type_id))
        ->orderBy('sort_order')->orderBy('name')->get();
    $spaceCategories = SpatialSpaceCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    $spaceTypes = SpatialSpaceType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    $spaceSubtypes = SpatialSpaceSubtype::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    return view('project-locations.edit-unit', compact('projectUnit', 'functionalUnitTypes', 'spaceCategories', 'spaceTypes', 'spaceSubtypes'));
}

public function updateUnit(Request $request, ProjectUnit $projectUnit)
{
    $this->ensureProjectAccess($projectUnit->project, $request->user());
    $data = $request->validate([
        'spatial_functional_unit_type_id' => 'nullable|exists:spatial_functional_unit_types,id',
        'identifier' => 'nullable|string|max:80',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);
    $oldValues = $projectUnit->toArray();
    $updates = ['is_active' => $data['is_active'], 'remarks' => $data['remarks'] ?? null];
    if (!empty($data['spatial_functional_unit_type_id'])) {
        $master = SpatialFunctionalUnitType::query()->whereKey($data['spatial_functional_unit_type_id'])
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $projectUnit->spatial_functional_unit_type_id))->firstOrFail();
        $identifier = trim((string) ($data['identifier'] ?? ''));
        if ($identifier === '') {
            return back()->withErrors(['identifier' => 'An identifier is required when selecting a functional unit type.'])->withInput();
        }
        $updates += [
            'spatial_functional_unit_type_id' => $master->id,
            'identifier' => $identifier,
            'name' => $this->generatedName($master->name, $master->name_pattern, $identifier),
            'type' => $master->name,
        ];
    }
    $projectUnit->update($updates);
    $this->auditProjectLocation('Unit Updated', 'ProjectUnit', $projectUnit,
        'Project unit updated: '.$projectUnit->name, $oldValues, $projectUnit->fresh()->toArray());
    return redirect()->route('project-locations.units.edit', $projectUnit)->with('success', 'Functional unit updated. Existing rooms preserved.');
}

public function toggleUnitStatus(ProjectUnit $projectUnit)
{
    $this->ensureProjectAccess($projectUnit->project, request()->user());
    $oldValues = $projectUnit->toArray();

    $projectUnit->update([
        'is_active' => !$projectUnit->is_active,
    ]);

    $newValues = $projectUnit->fresh()->toArray();

    $this->auditProjectLocation(
        $projectUnit->is_active
            ? 'Unit Activated'
            : 'Unit Deactivated',
        'ProjectUnit',
        $projectUnit,
        $projectUnit->is_active
            ? 'Project unit activated: ' . $projectUnit->name
            : 'Project unit deactivated: ' . $projectUnit->name,
        $oldValues,
        $newValues
    );

    return back()->with(
        'success',
        'Project unit status updated successfully.'
    );
}

public function editRoom(ProjectRoom $projectRoom)
{
    $this->ensureProjectAccess($projectRoom->project, request()->user());
    $projectRoom->load(['unit', 'floor.block']);
    $spaceCategories = SpatialSpaceCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    $spaceTypes = SpatialSpaceType::query()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $projectRoom->spatial_space_type_id))
        ->orderBy('sort_order')->orderBy('name')->get();
    $spaceSubtypes = SpatialSpaceSubtype::query()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $projectRoom->spatial_space_subtype_id))
        ->orderBy('sort_order')->orderBy('name')->get();
    return view('project-locations.edit-room', compact('projectRoom', 'spaceCategories', 'spaceTypes', 'spaceSubtypes'));
}

public function updateRoom(Request $request, ProjectRoom $projectRoom)
{
    $this->ensureProjectAccess($projectRoom->project, $request->user());
    $data = $request->validate([
        'spatial_space_type_id' => 'nullable|exists:spatial_space_types,id',
        'spatial_space_subtype_id' => 'nullable|exists:spatial_space_subtypes,id',
        'identifier' => 'nullable|string|max:80',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);
    $oldValues = $projectRoom->toArray();
    $updates = ['is_active' => $data['is_active'], 'remarks' => $data['remarks'] ?? null];
    if (!empty($data['spatial_space_type_id'])) {
        $type = SpatialSpaceType::query()->whereKey($data['spatial_space_type_id'])
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $projectRoom->spatial_space_type_id))->firstOrFail();
        $subtype = null;
        if (!empty($data['spatial_space_subtype_id'])) {
            $subtype = SpatialSpaceSubtype::query()->whereKey($data['spatial_space_subtype_id'])
                ->where('spatial_space_type_id', $type->id)
                ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $projectRoom->spatial_space_subtype_id))->firstOrFail();
        }
        $identifier = trim((string) ($data['identifier'] ?? ''));
        $baseName = $subtype?->name ?: $type->name;
        $updates += [
            'spatial_space_type_id' => $type->id,
            'spatial_space_subtype_id' => $subtype?->id,
            'identifier' => $identifier ?: null,
            'room_type' => $type->name,
            'name' => trim($baseName.($identifier !== '' ? ' '.$identifier : '')),
        ];
    }
    $projectRoom->update($updates);
    $this->auditProjectLocation('Room Updated', 'ProjectRoom', $projectRoom,
        'Project room updated: '.$projectRoom->name, $oldValues, $projectRoom->fresh()->toArray());
    return redirect()->route('project-locations.rooms.edit', $projectRoom)->with('success', 'Room updated. Existing geometry and elements preserved.');
}

public function toggleRoomStatus(ProjectRoom $projectRoom)
{
    $this->ensureProjectAccess($projectRoom->project, request()->user());
    $oldValues = $projectRoom->toArray();

    $projectRoom->update([
        'is_active' => !$projectRoom->is_active,
    ]);

    $newValues = $projectRoom->fresh()->toArray();

    $this->auditProjectLocation(
        $projectRoom->is_active
            ? 'Room Activated'
            : 'Room Deactivated',
        'ProjectRoom',
        $projectRoom,
        $projectRoom->is_active
            ? 'Project room activated: ' . $projectRoom->name
            : 'Project room deactivated: ' . $projectRoom->name,
        $oldValues,
        $newValues
    );

    return back()->with(
        'success',
        'Project room status updated successfully.'
    );
}

public function editSubspace(ProjectSubspace $projectSubspace)
{
    $this->ensureProjectAccess($projectSubspace->project, request()->user());
    $blocks = ProjectBlock::where('project_id', $projectSubspace->project_id)
        ->orderBy('name')
        ->get();

    $floors = ProjectFloor::where('project_id', $projectSubspace->project_id)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    $units = ProjectUnit::where('project_id', $projectSubspace->project_id)
        ->orderBy('name')
        ->get();

    $rooms = ProjectRoom::where('project_id', $projectSubspace->project_id)
        ->orderBy('name')
        ->get();

    $subspaceMasters = LocationSubspaceMaster::where('is_active', true)
        ->orderBy('type')
        ->orderBy('name')
        ->get();

    return view('project-locations.edit-subspace', compact(
        'projectSubspace',
        'blocks',
        'floors',
        'units',
        'rooms',
        'subspaceMasters'
    ));
}

public function updateSubspace(Request $request, ProjectSubspace $projectSubspace)
{
    $this->ensureProjectAccess($projectSubspace->project, $request->user());
    $request->validate([
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'project_unit_id' => 'required|exists:project_units,id',
        'project_room_id' => 'required|exists:project_rooms,id',
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

    $oldValues = $projectSubspace->toArray();

    $projectSubspace->update([
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'project_room_id' => $request->project_room_id,
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    $newValues = $projectSubspace->fresh()->toArray();

$this->auditProjectLocation(
    'Subspace Updated',
    'ProjectSubspace',
    $projectSubspace,
    'Project subspace updated: ' . $projectSubspace->name,
    $oldValues,
    $newValues
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectSubspace->project_id
        ])
        ->with('success', 'Project sub-space updated successfully.');
}

public function toggleSubspaceStatus(ProjectSubspace $projectSubspace)
{
    $this->ensureProjectAccess($projectSubspace->project, request()->user());
    $oldValues = $projectSubspace->toArray();

    $projectSubspace->update([
        'is_active' => !$projectSubspace->is_active,
    ]);

    $newValues = $projectSubspace->fresh()->toArray();

    $this->auditProjectLocation(
        $projectSubspace->is_active
            ? 'Subspace Activated'
            : 'Subspace Deactivated',
        'ProjectSubspace',
        $projectSubspace,
        $projectSubspace->is_active
            ? 'Project subspace activated: ' . $projectSubspace->name
            : 'Project subspace deactivated: ' . $projectSubspace->name,
        $oldValues,
        $newValues
    );

    return back()->with(
        'success',
        'Project sub-space status updated successfully.'
    );
}

public function wizard(Project $project)
{
    $this->ensureProjectAccess($project, request()->user());
    $blockMasters = LocationBlockMaster::where('is_active', true)->orderBy('name')->get();
    $floorMasters = LocationFloorMaster::where('is_active', true)->orderBy('sequence')->orderBy('name')->get();
    $roomMasters = LocationRoomMaster::where('is_active', true)->orderBy('room_type')->orderBy('name')->get();
    $subspaceMasters = LocationSubspaceMaster::where('is_active', true)->orderBy('type')->orderBy('name')->get();

    return view('project-locations.wizard', compact(
        'project',
        'blockMasters',
        'floorMasters',
        'roomMasters',
        'subspaceMasters'
    ));
}

public function generateWizard(Request $request, Project $project)
{
    $this->ensureProjectAccess($project, $request->user());
    $request->validate([
        'project_type' => 'required|string',
        'block_type' => 'required|in:Block,Building,Tower,Villa,External Area,Not Applicable',
        'blocks' => 'required|integer|min:1|max:20',
        'units_per_floor' => 'required|integer|min:0|max:100',

        'parking_type' => 'required|in:Ground Parking,Cellar Parking,No Parking',
        'cellars' => 'nullable|integer|min:0|max:100',
        'residential_floors' => 'required|integer|min:0|max:100',
        'ground_has_residential' => 'required|boolean',

        'shops' => 'nullable|integer|min:0|max:100',
        'has_watchman_room' => 'required|boolean',
        'has_security_room' => 'required|boolean',
        'has_ground_washroom' => 'required|boolean',
        'has_electrical_room' => 'required|boolean',
        'has_pump_room' => 'required|boolean',
        'has_meter_room' => 'required|boolean',
        'has_dg_room' => 'required|boolean',

        'bedrooms' => 'nullable|integer|min:0|max:20',
        'has_master_bedroom' => 'required|boolean',
        'bathrooms' => 'nullable|integer|min:0|max:20',
        'balconies' => 'nullable|integer|min:0|max:20',

        'has_living' => 'required|boolean',
        'has_dining' => 'required|boolean',
        'has_kitchen' => 'required|boolean',
        'has_utility' => 'required|boolean',
        'has_pooja' => 'required|boolean',
        'has_study' => 'required|boolean',
        'has_store' => 'required|boolean',
        'has_home_office' => 'required|boolean',
    ]);

    $createdCounts = [
        'blocks' => 0,
        'floors' => 0,
        'units' => 0,
        'rooms' => 0,
        'subspaces' => 0,
    ];

    DB::transaction(function () use ($request, $project, &$createdCounts) {

        $subspaceMasters = LocationSubspaceMaster::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $cellars = $request->parking_type === 'Cellar Parking'
            ? (int) ($request->cellars ?? 1)
            : 0;

        $residentialFloors = (int) $request->residential_floors;
        $unitsPerFloor = (int) $request->units_per_floor;

        for ($b = 1; $b <= (int) $request->blocks; $b++) {

            $blockName = $request->block_type === 'Villa'
                ? 'Villa ' . $b
                : 'Block ' . chr(64 + $b);

            $block = ProjectBlock::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'name' => $blockName,
                ],
                [
                    'code' => strtoupper(str_replace(' ', '-', $blockName)),
                    'type' => $request->block_type,
                    'is_active' => true,
                    'remarks' => 'Generated by structure wizard',
                ]
            );

            if ($block->wasRecentlyCreated) {
                $createdCounts['blocks']++;
            }

            // Cellars
            for ($c = $cellars; $c >= 1; $c--) {
                $floor = $this->createWizardFloor(
                    $project,
                    $block,
                    'Cellar ' . $c,
                    -$c,
                    'Parking',
                    $createdCounts
                );

                $this->createServiceUnitWithRooms(
                    $project,
                    $block,
                    $floor,
                    'Parking Zone',
                    'Parking',
                    [
                        ['name' => 'Parking Area', 'type' => 'Parking'],
                    ],
                    $subspaceMasters,
                    $createdCounts
                );
            }

            // Ground Floor
            $groundUsage = 'Residential Flats';

            if ($request->parking_type === 'Ground Parking') {
                $groundUsage = ((int) $request->ground_has_residential === 1)
                    ? 'Mixed Use'
                    : 'Parking';
            } elseif ((int) $request->ground_has_residential !== 1) {
                $groundUsage = 'Service Area';
            }

            $groundFloor = $this->createWizardFloor(
                $project,
                $block,
                'Ground Floor',
                0,
                $groundUsage,
                $createdCounts
            );

            // Ground extras
            $groundRooms = [];

            if ($request->parking_type === 'Ground Parking') {
                $groundRooms[] = ['name' => 'Parking Area', 'type' => 'Parking'];
            }

            for ($s = 1; $s <= (int) ($request->shops ?? 0); $s++) {
                $groundRooms[] = ['name' => 'Shop ' . $s, 'type' => 'Commercial'];
            }

            if ((int) $request->has_watchman_room === 1) {
                $groundRooms[] = ['name' => 'Watchman Room', 'type' => 'Service Area'];
            }

            if ((int) $request->has_security_room === 1) {
                $groundRooms[] = ['name' => 'Security Room', 'type' => 'Service Area'];
            }

            if ((int) $request->has_ground_washroom === 1) {
                $groundRooms[] = ['name' => 'Washroom', 'type' => 'Toilet / Bathroom Spaces'];
            }

            if ((int) $request->has_electrical_room === 1) {
                $groundRooms[] = ['name' => 'Electrical Room', 'type' => 'Service Area'];
            }

            if ((int) $request->has_pump_room === 1) {
                $groundRooms[] = ['name' => 'Pump Room', 'type' => 'Service Area'];
            }

            if ((int) $request->has_meter_room === 1) {
                $groundRooms[] = ['name' => 'Meter Room', 'type' => 'Service Area'];
            }

            if ((int) $request->has_dg_room === 1) {
                $groundRooms[] = ['name' => 'DG Room', 'type' => 'Service Area'];
            }

            if (count($groundRooms)) {
                $this->createServiceUnitWithRooms(
                    $project,
                    $block,
                    $groundFloor,
                    'Ground Floor Common Area',
                    'Common Area',
                    $groundRooms,
                    $subspaceMasters,
                    $createdCounts
                );
            }

            // Ground residential flats if enabled
            if ((int) $request->ground_has_residential === 1 && $unitsPerFloor > 0) {
                $this->createResidentialUnitsForFloor(
                    $project,
                    $block,
                    $groundFloor,
                    0,
                    $unitsPerFloor,
                    $request,
                    $subspaceMasters,
                    $createdCounts
                );
            }

            // Residential floors above ground: Floor 1 to Floor N
            for ($f = 1; $f <= $residentialFloors; $f++) {
                $floor = $this->createWizardFloor(
                    $project,
                    $block,
                    'Floor ' . $f,
                    $f,
                    'Residential Flats',
                    $createdCounts
                );

                if ($unitsPerFloor > 0) {
                    $this->createResidentialUnitsForFloor(
                        $project,
                        $block,
                        $floor,
                        $f,
                        $unitsPerFloor,
                        $request,
                        $subspaceMasters,
                        $createdCounts
                    );
                }
            }
        }

        $this->auditProjectLocation(
            'Structure Generated',
            'Project',
            $project,
            'Project structure generated using simple wizard',
            null,
            $createdCounts
        );
    });

    return redirect()
        ->route('project-locations.index', ['project_id' => $project->id])
        ->with(
            'success',
            'Structure generated successfully. Blocks: ' . $createdCounts['blocks'] .
            ', Floors: ' . $createdCounts['floors'] .
            ', Units: ' . $createdCounts['units'] .
            ', Rooms: ' . $createdCounts['rooms'] .
            ', Sub-spaces: ' . $createdCounts['subspaces']
        );
}

private function createWizardFloor(
    Project $project,
    ProjectBlock $block,
    string $name,
    int $sequence,
    string $usageType,
    array &$createdCounts
) {
    $floor = ProjectFloor::firstOrCreate(
        [
            'project_id' => $project->id,
            'project_block_id' => $block->id,
            'name' => $name,
        ],
        [
            'sequence' => $sequence,
            'usage_type' => $usageType,
            'is_active' => true,
            'remarks' => 'Generated by structure wizard',
        ]
    );

    if ($floor->wasRecentlyCreated) {
        $createdCounts['floors']++;
    }

    return $floor;
}

private function createResidentialUnitsForFloor(
    Project $project,
    ProjectBlock $block,
    ProjectFloor $floor,
    int $floorNumber,
    int $unitsPerFloor,
    Request $request,
    $subspaceMasters,
    array &$createdCounts
) {
    for ($u = 1; $u <= $unitsPerFloor; $u++) {
        $unitNumber = $floorNumber === 0
            ? str_pad($u, 3, '0', STR_PAD_LEFT)
            : ($floorNumber * 100) + $u;

        $unit = ProjectUnit::firstOrCreate(
            [
                'project_id' => $project->id,
                'project_block_id' => $block->id,
                'project_floor_id' => $floor->id,
                'name' => 'Flat ' . $unitNumber,
            ],
            [
                'type' => 'Flat / Unit',
                'is_active' => true,
                'remarks' => 'Generated by structure wizard',
            ]
        );

        if ($unit->wasRecentlyCreated) {
            $createdCounts['units']++;
        }

        $rooms = $this->buildFlatRooms($request);

        foreach ($rooms as $roomData) {
            $this->createRoomWithSubspaces(
                $project,
                $block,
                $floor,
                $unit,
                $roomData['name'],
                $roomData['type'],
                $subspaceMasters,
                $createdCounts
            );
        }
    }
}

private function buildFlatRooms(Request $request): array
{
    $rooms = [];

    if ((int) $request->has_living === 1) {
        $rooms[] = ['name' => 'Living Room', 'type' => 'Living / Common Spaces'];
    }

    if ((int) $request->has_dining === 1) {
        $rooms[] = ['name' => 'Dining', 'type' => 'Living / Common Spaces'];
    }

    if ((int) $request->has_kitchen === 1) {
        $rooms[] = ['name' => 'Kitchen', 'type' => 'Kitchen / Utility Spaces'];
    }

    if ((int) $request->has_utility === 1) {
        $rooms[] = ['name' => 'Utility', 'type' => 'Kitchen / Utility Spaces'];
    }

    if ((int) $request->has_pooja === 1) {
        $rooms[] = ['name' => 'Pooja Room', 'type' => 'Living / Common Spaces'];
    }

    if ((int) $request->has_study === 1) {
        $rooms[] = ['name' => 'Study Room', 'type' => 'Living / Common Spaces'];
    }

    if ((int) $request->has_store === 1) {
        $rooms[] = ['name' => 'Store Room', 'type' => 'Kitchen / Utility Spaces'];
    }

    if ((int) $request->has_home_office === 1) {
        $rooms[] = ['name' => 'Home Office', 'type' => 'Living / Common Spaces'];
    }

    $bedrooms = (int) ($request->bedrooms ?? 0);

    for ($i = 1; $i <= $bedrooms; $i++) {
        if ((int) $request->has_master_bedroom === 1 && $i === 1) {
            $rooms[] = ['name' => 'Master Bedroom', 'type' => 'Bedroom Spaces'];
        } else {
            $rooms[] = ['name' => 'Bedroom ' . $i, 'type' => 'Bedroom Spaces'];
        }
    }

    $bathrooms = (int) ($request->bathrooms ?? 0);

    for ($i = 1; $i <= $bathrooms; $i++) {
        $rooms[] = ['name' => 'Bathroom ' . $i, 'type' => 'Toilet / Bathroom Spaces'];
    }

    $balconies = (int) ($request->balconies ?? 0);

    for ($i = 1; $i <= $balconies; $i++) {
        $rooms[] = ['name' => 'Balcony ' . $i, 'type' => 'External / Common Spaces'];
    }

    return $rooms;
}

private function createServiceUnitWithRooms(
    Project $project,
    ProjectBlock $block,
    ProjectFloor $floor,
    string $unitName,
    string $unitType,
    array $rooms,
    $subspaceMasters,
    array &$createdCounts
) {
    $unit = ProjectUnit::firstOrCreate(
        [
            'project_id' => $project->id,
            'project_block_id' => $block->id,
            'project_floor_id' => $floor->id,
            'name' => $unitName,
        ],
        [
            'type' => $unitType,
            'is_active' => true,
            'remarks' => 'Generated by structure wizard',
        ]
    );

    if ($unit->wasRecentlyCreated) {
        $createdCounts['units']++;
    }

    foreach ($rooms as $roomData) {
        $this->createRoomWithSubspaces(
            $project,
            $block,
            $floor,
            $unit,
            $roomData['name'],
            $roomData['type'],
            $subspaceMasters,
            $createdCounts
        );
    }
}

private function createRoomWithSubspaces(
    Project $project,
    ProjectBlock $block,
    ProjectFloor $floor,
    ProjectUnit $unit,
    string $roomName,
    string $roomType,
    $subspaceMasters,
    array &$createdCounts
) {
    $room = ProjectRoom::firstOrCreate(
        [
            'project_id' => $project->id,
            'project_block_id' => $block->id,
            'project_floor_id' => $floor->id,
            'project_unit_id' => $unit->id,
            'name' => $roomName,
        ],
        [
            'room_type' => $roomType,
            'is_active' => true,
            'remarks' => 'Generated by structure wizard',
        ]
    );

    if ($room->wasRecentlyCreated) {
        $createdCounts['rooms']++;
    }

    foreach ($subspaceMasters as $subspaceMaster) {
        $subspace = ProjectSubspace::firstOrCreate(
            [
                'project_id' => $project->id,
                'project_block_id' => $block->id,
                'project_floor_id' => $floor->id,
                'project_unit_id' => $unit->id,
                'project_room_id' => $room->id,
                'name' => $subspaceMaster->name,
            ],
            [
                'type' => $subspaceMaster->type,
                'is_active' => true,
                'remarks' => 'Generated by structure wizard',
            ]
        );

        if ($subspace->wasRecentlyCreated) {
            $createdCounts['subspaces']++;
        }
    }
}

public function convertFloorUsage(Request $request, ProjectFloor $projectFloor)
{
    $this->ensureProjectAccess($projectFloor->project, $request->user());

    $validated = $request->validate([
        'usage_type' => 'required|string|max:100',
    ]);

    $oldValues = $projectFloor->toArray();
    $projectFloor->update(['usage_type' => $validated['usage_type']]);
    $newValues = $projectFloor->fresh()->toArray();

    $this->auditProjectLocation(
        'Floor Usage Changed', 'ProjectFloor', $projectFloor,
        'Floor usage changed: ' . $projectFloor->name . ' → ' . $validated['usage_type'],
        $oldValues, $newValues
    );

    return back()->with('success', 'Floor usage updated. Existing units, rooms and spaces were preserved.');
}

private function auditProjectLocation(
    string $action,
    string $recordType,
    $record,
    string $description,
    $oldValues = null,
    $newValues = null
) {
    AuditHelper::log(
        'Project Locations',
        $action,
        $recordType,
        $record->id,
        $description,
        $oldValues,
        $newValues
    );
}

public function ajaxBlocks(Project $project)
{
    $this->ensureProjectAccess($project, request()->user());
    return response()->json(ProjectBlock::where('project_id', $project->id)->where('is_active', true)->orderBy('name')->get(['id', 'name']));
}

public function ajaxFloors(ProjectBlock $block)
{
    $this->ensureProjectAccess($block->project, request()->user());
    return response()->json(ProjectFloor::where('project_block_id', $block->id)->where('project_id', $block->project_id)->where('is_active', true)->orderBy('sequence')->orderBy('name')->get(['id', 'name']));
}

public function ajaxUnits(ProjectFloor $floor)
{
    $this->ensureProjectAccess($floor->project, request()->user());
    return response()->json(ProjectUnit::where('project_floor_id', $floor->id)->where('project_id', $floor->project_id)->where('is_active', true)->orderBy('name')->get(['id', 'name']));
}

public function ajaxRooms(ProjectUnit $unit)
{
    $this->ensureProjectAccess($unit->project, request()->user());
    return response()->json(ProjectRoom::where('project_unit_id', $unit->id)->where('project_id', $unit->project_id)->where('is_active', true)->orderBy('room_type')->orderBy('name')->get(['id', 'name']));
}

public function ajaxSubspaces(ProjectRoom $room)
{
    $this->ensureProjectAccess($room->project, request()->user());
    return response()->json(ProjectSubspace::where('project_room_id', $room->id)->where('project_id', $room->project_id)->where('is_active', true)->orderBy('type')->orderBy('name')->get(['id', 'name']));
}

private function generatedName(string $fallbackName, ?string $pattern, string $identifier): string
{
    $pattern = trim((string) $pattern);
    if ($pattern === '') {
        return trim($fallbackName.($identifier !== '' ? ' '.$identifier : ''));
    }

    if (str_contains($pattern, '{n}')) {
        return trim(str_replace('{n}', $identifier, $pattern));
    }

    if (str_contains($pattern, '{id}')) {
        return trim(str_replace('{id}', $identifier, $pattern));
    }

    if (str_contains($pattern, '{custom}')) {
        return $identifier !== '' ? $identifier : $fallbackName;
    }

    return trim($pattern.($identifier !== '' ? ' '.$identifier : ''));
}

private function accessibleProjectsQuery($user)
{
    $query = Project::query();
    $roleName = optional($user->role)->name;

    if (in_array($roleName, ['Admin', 'CEO'], true) || $user->hasAllProjectAccess()) {
        return $query;
    }

    return $query->whereHas('users', fn ($q) => $q->where('users.id', $user->id));
}

private function ensureProjectAccess(Project $project, $user): void
{
    $roleName = optional($user->role)->name;

    if (in_array($roleName, ['Admin', 'CEO'], true) || $user->hasAllProjectAccess()) {
        return;
    }

    abort_unless($user->hasProjectAccess((int) $project->id), 403, 'You do not have access to this project.');
}

}