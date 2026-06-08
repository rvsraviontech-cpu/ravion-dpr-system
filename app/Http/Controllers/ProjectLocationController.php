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

    ProjectBlock::create([
        'project_id' => $request->project_id,
        'name' => $request->name,
        'code' => $request->code,
        'type' => $request->type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

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

    ProjectFloor::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'name' => $request->name,
        'sequence' => $request->sequence ?? 0,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

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

    ProjectUnit::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

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

    ProjectRoom::create([
        'project_id' => $request->project_id,
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'name' => $request->name,
        'room_type' => $request->room_type,
        'is_active' => true,
        'remarks' => $request->remarks,
    ]);

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

    ProjectSubspace::create([
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

    $projectBlock->update([
        'name' => $request->name,
        'code' => $request->code,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectBlock->project_id
        ])
        ->with('success', 'Project block updated successfully.');
}

public function toggleBlockStatus(ProjectBlock $projectBlock)
{
    $projectBlock->update([
        'is_active' => !$projectBlock->is_active,
    ]);

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

    $projectFloor->update([
        'project_block_id' => $request->project_block_id,
        'name' => $request->name,
        'sequence' => $request->sequence ?? 0,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectFloor->project_id
        ])
        ->with('success', 'Project floor updated successfully.');
}

public function toggleFloorStatus(ProjectFloor $projectFloor)
{
    $projectFloor->update([
        'is_active' => !$projectFloor->is_active,
    ]);

    return back()->with('success', 'Project floor status updated successfully.');
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

    $projectUnit->update([
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectUnit->project_id
        ])
        ->with('success', 'Project unit updated successfully.');
}

public function toggleUnitStatus(ProjectUnit $projectUnit)
{
    $projectUnit->update([
        'is_active' => !$projectUnit->is_active,
    ]);

    return back()->with('success', 'Project unit status updated successfully.');
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

    $projectRoom->update([
        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'name' => $request->name,
        'room_type' => $request->room_type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectRoom->project_id
        ])
        ->with('success', 'Project room updated successfully.');
}

public function toggleRoomStatus(ProjectRoom $projectRoom)
{
    $projectRoom->update([
        'is_active' => !$projectRoom->is_active,
    ]);

    return back()->with('success', 'Project room status updated successfully.');
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

    return redirect()
        ->route('project-locations.index', [
            'project_id' => $projectSubspace->project_id
        ])
        ->with('success', 'Project sub-space updated successfully.');
}

public function toggleSubspaceStatus(ProjectSubspace $projectSubspace)
{
    $projectSubspace->update([
        'is_active' => !$projectSubspace->is_active,
    ]);

    return back()->with('success', 'Project sub-space status updated successfully.');
}
}