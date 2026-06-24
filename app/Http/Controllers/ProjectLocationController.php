<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
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
    $projects = Project::orderBy('project_name')->get();

    $selectedProjectId = $request->project_id;

    $selectedProject = null;
    $blocks = collect();
    $floors = collect();
    $units = collect();
    $rooms = collect();
    $subspaces = collect();

    if ($selectedProjectId) {
        $selectedProject = Project::find($selectedProjectId);

        $blocks = ProjectBlock::where('project_id', $selectedProjectId)
            ->orderBy('name')
            ->get();

        $floors = ProjectFloor::with('block')
            ->where('project_id', $selectedProjectId)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $units = ProjectUnit::with(['block', 'floor'])
    ->where('project_id', $selectedProjectId)
    ->where('is_active', true)
    ->orderBy('project_floor_id')
    ->orderBy('name')
    ->get();

$rooms = ProjectRoom::with(['block', 'floor', 'unit'])
    ->where('project_id', $selectedProjectId)
    ->where('is_active', true)
    ->orderBy('project_unit_id')
    ->orderBy('room_type')
    ->orderBy('name')
    ->get();

$subspaces = ProjectSubspace::with(['block', 'floor', 'unit', 'room'])
    ->where('project_id', $selectedProjectId)
    ->where('is_active', true)
    ->orderBy('project_room_id')
    ->orderBy('type')
    ->orderBy('name')
    ->get();
    }

    $blockMasters = LocationBlockMaster::where('is_active', true)
        ->orderBy('name')
        ->get();

    $floorMasters = LocationFloorMaster::where('is_active', true)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    $unitMasters = LocationUnitMaster::where('is_active', true)
        ->orderBy('name')
        ->get();

    $roomMasters = LocationRoomMaster::where('is_active', true)
        ->orderBy('room_type')
        ->orderBy('name')
        ->get();

    $subspaceMasters = LocationSubspaceMaster::where('is_active', true)
        ->orderBy('type')
        ->orderBy('name')
        ->get();

    return view('project-locations.index', compact(
        'projects',
        'selectedProject',
        'selectedProjectId',
        'blocks',
        'floors',
        'units',
        'rooms',
        'subspaces',
        'blockMasters',
        'floorMasters',
        'unitMasters',
        'roomMasters',
        'subspaceMasters'
    ));
}

    // STORE BLOCK

    public function storeBlock(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:255',
        'type' => 'required|string|max:255',
        'remarks' => 'nullable|string',
    ]);

    $projectBlock = ProjectBlock::create([
        'project_id' => $request->project_id,
        'name' => $request->name,
        'code' => $request->code,
        'type' => $request->type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

    $this->auditProjectLocation(
    'Block Created',
    'ProjectBlock',
    $projectBlock,
    'Project block created: ' . $projectBlock->name,
    null,
    $projectBlock->toArray()
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $request->project_id
        ])
        ->with('success', 'Block / Building added successfully.');
}

// STORE FLOOR

public function storeFloor(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'name' => 'required|string|max:255',
        'sequence' => 'nullable|integer',
        'remarks' => 'nullable|string',
    ]);

    $projectFloor = ProjectFloor::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'name' => $request->name,
        'sequence' => $request->sequence ?? 0,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

    $this->auditProjectLocation(
    'Floor Created',
    'ProjectFloor',
    $projectFloor,
    'Project floor created: ' . $projectFloor->name,
    null,
    $projectFloor->toArray()
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $request->project_id
        ])
        ->with('success', 'Floor added successfully.');
}

// STORE UNIT

public function storeUnit(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'remarks' => 'nullable|string',
    ]);

    $projectUnit = ProjectUnit::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);
    $this->auditProjectLocation(
    'Unit Created',
    'ProjectUnit',
    $projectUnit,
    'Project unit created: ' . $projectUnit->name,
    null,
    $projectUnit->toArray()
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $request->project_id
        ])
        ->with('success', 'Unit / Flat / Villa added successfully.');
}

// STORE ROOM

public function storeRoom(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'project_unit_id' => 'required|exists:project_units,id',
        'name' => 'required|string|max:255',
        'room_type' => 'nullable|string|max:255',
        'remarks' => 'nullable|string',
    ]);

    $projectRoom = ProjectRoom::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'name' => $request->name,
        'room_type' => $request->room_type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

    $this->auditProjectLocation(
    'Room Created',
    'ProjectRoom',
    $projectRoom,
    'Project room created: ' . $projectRoom->name,
    null,
    $projectRoom->toArray()
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $request->project_id
        ])
        ->with('success', 'Room / Space added successfully.');
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
    $blocks = ProjectBlock::where('project_id', $projectUnit->project_id)
        ->orderBy('name')
        ->get();

    $floors = ProjectFloor::where('project_id', $projectUnit->project_id)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    $unitMasters = LocationUnitMaster::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('project-locations.edit-unit', compact(
        'projectUnit',
        'blocks',
        'floors',
        'unitMasters'
    ));
}

public function updateUnit(Request $request, ProjectUnit $projectUnit)
{
    $request->validate([
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

    $oldValues = $projectUnit->toArray();

    $projectUnit->update([
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);
    $newValues = $projectUnit->fresh()->toArray();

$this->auditProjectLocation(
    'Unit Updated',
    'ProjectUnit',
    $projectUnit,
    'Project unit updated: ' . $projectUnit->name,
    $oldValues,
    $newValues
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectUnit->project_id
        ])
        ->with('success', 'Project unit updated successfully.');
}

public function toggleUnitStatus(ProjectUnit $projectUnit)
{
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
    $blocks = ProjectBlock::where('project_id', $projectRoom->project_id)
        ->orderBy('name')
        ->get();

    $floors = ProjectFloor::where('project_id', $projectRoom->project_id)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    $units = ProjectUnit::where('project_id', $projectRoom->project_id)
        ->orderBy('name')
        ->get();

    $roomMasters = LocationRoomMaster::where('is_active', true)
        ->orderBy('room_type')
        ->orderBy('name')
        ->get();

    return view('project-locations.edit-room', compact(
        'projectRoom',
        'blocks',
        'floors',
        'units',
        'roomMasters'
    ));
}

public function updateRoom(Request $request, ProjectRoom $projectRoom)
{
    $request->validate([
        'project_block_id' => 'required|exists:project_blocks,id',
        'project_floor_id' => 'required|exists:project_floors,id',
        'project_unit_id' => 'required|exists:project_units,id',
        'name' => 'required|string|max:255',
        'room_type' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);
    $oldValues = $projectRoom->toArray();

    $projectRoom->update([
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'name' => $request->name,
        'room_type' => $request->room_type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    $newValues = $projectRoom->fresh()->toArray();

$this->auditProjectLocation(
    'Room Updated',
    'ProjectRoom',
    $projectRoom,
    'Project room updated: ' . $projectRoom->name,
    $oldValues,
    $newValues
);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectRoom->project_id
        ])
        ->with('success', 'Project room updated successfully.');
}

public function toggleRoomStatus(ProjectRoom $projectRoom)
{
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
    $request->validate([
        'project_type' => 'required|string',
        'block_type' => 'required|in:Block,Building,Tower,Villa,External Area,Not Applicable',
        'blocks' => 'required|integer|min:1|max:20',
        'units_per_floor' => 'required|integer|min:0|max:100',

        'parking_type' => 'required|in:Ground Parking,Cellar Parking,No Parking',
        'cellars' => 'nullable|integer|min:0|max:5',
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
    $request->validate([
        'usage_type' => 'required|in:Residential Flats,Parking,Shops / Commercial,Amenities,Service Area,Mixed Use',
    ]);

    $oldValues = $projectFloor->toArray();

    $projectFloor->update([
        'usage_type' => $request->usage_type,
        'remarks' => trim(($projectFloor->remarks ?? '') . "\nUsage changed to: " . $request->usage_type),
    ]);

    if ($request->usage_type !== 'Residential Flats') {
        ProjectUnit::where('project_floor_id', $projectFloor->id)->update([
            'is_active' => false,
        ]);

        ProjectRoom::where('project_floor_id', $projectFloor->id)->update([
            'is_active' => false,
        ]);

        ProjectSubspace::where('project_floor_id', $projectFloor->id)->update([
            'is_active' => false,
        ]);
    }

    $newValues = $projectFloor->fresh()->toArray();

    $this->auditProjectLocation(
        'Floor Usage Changed',
        'ProjectFloor',
        $projectFloor,
        'Floor usage changed: ' . $projectFloor->name . ' → ' . $request->usage_type,
        $oldValues,
        $newValues
    );

    return back()->with('success', 'Floor usage updated successfully.');
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
    return response()->json(
        ProjectBlock::where('project_id', $project->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}

public function ajaxFloors(ProjectBlock $block)
{
    return response()->json(
        ProjectFloor::where('project_block_id', $block->id)
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}

public function ajaxUnits(ProjectFloor $floor)
{
    return response()->json(
        ProjectUnit::where('project_floor_id', $floor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}

public function ajaxRooms(ProjectUnit $unit)
{
    return response()->json(
        ProjectRoom::where('project_unit_id', $unit->id)
            ->where('is_active', true)
            ->orderBy('room_type')
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}

public function ajaxSubspaces(ProjectRoom $room)
{
    return response()->json(
        ProjectSubspace::where('project_room_id', $room->id)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}
}