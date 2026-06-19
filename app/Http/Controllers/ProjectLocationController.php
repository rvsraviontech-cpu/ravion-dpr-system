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

class ProjectLocationController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('project_name')->get();

        $selectedProjectId = $request->project_id;

        $blocks = collect();
        $floors = collect();
        $units = collect();
        $rooms = collect();
        $subspaces = collect();

        if ($selectedProjectId) {
            $blocks = ProjectBlock::where('project_id', $selectedProjectId)
                ->orderBy('name')
                ->get();

            $floors = ProjectFloor::where('project_id', $selectedProjectId)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get();

            $units = ProjectUnit::where('project_id', $selectedProjectId)
                ->orderBy('name')
                ->get();

            $rooms = ProjectRoom::where('project_id', $selectedProjectId)
                ->orderBy('name')
                ->get();

            $subspaces = ProjectSubspace::where('project_id', $selectedProjectId)
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
}