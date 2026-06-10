<?php

namespace App\Http\Controllers;

use App\Models\TomorrowPlan;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\Activity;
use App\Models\Contractor;
use Illuminate\Http\Request;
use App\Models\ActivityDivision;

class TomorrowPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = TomorrowPlan::with([
            'project', 'block', 'floor', 'unit', 'room',
            'subspace', 'activity', 'contractor', 'creator', 'approver',
        ])->latest();

        if ($request->filled('planned_date')) {
            $query->whereDate('planned_date', $request->planned_date);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tomorrowPlans = $query->paginate(10);

        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        return view('tomorrow-plans.index', compact('tomorrowPlans', 'projects'));
    }

    public function create()
    {
        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
        $projectFloors = ProjectFloor::where('is_active', true)->orderBy('name')->get();
        $projectUnits = ProjectUnit::where('is_active', true)->orderBy('name')->get();
        $projectRooms = ProjectRoom::orderBy('name')->get();
        $projectSubspaces = ProjectSubspace::where('is_active', true)->orderBy('name')->get();
        $contractors = Contractor::where('status', 'Active')->orderBy('contractor_name')->get();
        $activityDivisions = ActivityDivision::where('is_active', 1) ->orderBy('name') ->get();
        $activities = Activity::where('is_active', 1) ->orderBy('activity_name') ->get();

        return view('tomorrow-plans.create', compact(
            'projects',
            'projectBlocks',
            'projectFloors',
            'projectUnits',
            'projectRooms',
            'projectSubspaces',
            'activities',
            'contractors',
            'activityDivisions'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_block_id' => 'nullable|exists:project_blocks,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'project_room_id' => 'nullable|exists:project_rooms,id',
            'project_subspace_id' => 'nullable|exists:project_subspaces,id',
            'activity_id' => 'required|exists:activities,id',
            'contractor_id' => 'nullable|exists:contractors,id',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'planned_labour' => 'nullable|integer|min:0',
            'required_skilled_labour' => 'nullable|integer|min:0',
            'required_semiskilled_labour' => 'nullable|integer|min:0',
            'required_helpers' => 'nullable|integer|min:0',
            'materials_required' => 'nullable|string',
            'machinery_required' => 'nullable|string',
            'drawing_required' => 'required|boolean',
            'client_approval_required' => 'required|boolean',
            'responsible_person' => 'nullable|string|max:255',
            'planned_date' => 'required|date',
            'priority' => 'required|string|max:50',
            'risks_constraints' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        TomorrowPlan::create([
            'project_id' => $request->project_id,
            'project_block_id' => $request->project_block_id,
            'project_floor_id' => $request->project_floor_id,
            'project_unit_id' => $request->project_unit_id,
            'project_room_id' => $request->project_room_id,
            'project_subspace_id' => $request->project_subspace_id,
            'activity_id' => $request->activity_id,
            'contractor_id' => $request->contractor_id,
            'planned_quantity' => $request->planned_quantity,
            'unit' => $request->unit,
            'planned_labour' => $request->planned_labour ?? 0,
            'required_skilled_labour' => $request->required_skilled_labour ?? 0,
            'required_semiskilled_labour' => $request->required_semiskilled_labour ?? 0,
            'required_helpers' => $request->required_helpers ?? 0,
            'materials_required' => $request->materials_required,
            'machinery_required' => $request->machinery_required,
            'drawing_required' => $request->drawing_required,
            'client_approval_required' => $request->client_approval_required,
            'responsible_person' => $request->responsible_person,
            'planned_date' => $request->planned_date,
            'priority' => $request->priority,
            'status' => 'Draft',
            'created_by' => auth()->id(),
            'risks_constraints' => $request->risks_constraints,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('tomorrow-plans.index')
            ->with('success', 'Tomorrow plan created successfully.');
    }

    public function show(TomorrowPlan $tomorrowPlan)
    {
        $tomorrowPlan->load([
            'project', 'block', 'floor', 'unit', 'room',
            'subspace', 'activity', 'contractor', 'creator', 'approver',
        ]);

        return view('tomorrow-plans.show', compact('tomorrowPlan'));
    }

    public function edit(TomorrowPlan $tomorrowPlan)
    {
        if ($tomorrowPlan->status !== 'Draft') {
            return redirect()
                ->route('tomorrow-plans.index')
                ->with('error', 'Only draft tomorrow plans can be edited.');
        }

        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
        $projectFloors = ProjectFloor::where('is_active', true)->orderBy('name')->get();
        $projectUnits = ProjectUnit::where('is_active', true)->orderBy('name')->get();
        $projectRooms = ProjectRoom::orderBy('name')->get();
        $projectSubspaces = ProjectSubspace::where('is_active', true)->orderBy('name')->get();
        $activities = Activity::where('is_active', 1)->orderBy('activity_name')->get();
        $activityDivisions = ActivityDivision::where('is_active', 1)->orderBy('name')->get();
        $contractors = Contractor::where('status', 'Active')->orderBy('contractor_name')->get();

        return view('tomorrow-plans.edit', compact(
            'tomorrowPlan',
            'projects',
            'projectBlocks',
            'projectFloors',
            'projectUnits',
            'projectRooms',
            'projectSubspaces',
            'activities',
            'contractors',
            'activityDivisions'
        ));
    }

    public function update(Request $request, TomorrowPlan $tomorrowPlan)
    {
        if ($tomorrowPlan->status !== 'Draft') {
            return redirect()
                ->route('tomorrow-plans.index')
                ->with('error', 'Only draft tomorrow plans can be updated.');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_block_id' => 'nullable|exists:project_blocks,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'project_room_id' => 'nullable|exists:project_rooms,id',
            'project_subspace_id' => 'nullable|exists:project_subspaces,id',
            'activity_id' => 'required|exists:activities,id',
            'contractor_id' => 'nullable|exists:contractors,id',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'planned_labour' => 'nullable|integer|min:0',
            'required_skilled_labour' => 'nullable|integer|min:0',
            'required_semiskilled_labour' => 'nullable|integer|min:0',
            'required_helpers' => 'nullable|integer|min:0',
            'materials_required' => 'nullable|string',
            'machinery_required' => 'nullable|string',
            'drawing_required' => 'required|boolean',
            'client_approval_required' => 'required|boolean',
            'responsible_person' => 'nullable|string|max:255',
            'planned_date' => 'required|date',
            'priority' => 'required|string|max:50',
            'risks_constraints' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $tomorrowPlan->update($request->only([
            'project_id',
            'project_block_id',
            'project_floor_id',
            'project_unit_id',
            'project_room_id',
            'project_subspace_id',
            'activity_id',
            'contractor_id',
            'planned_quantity',
            'unit',
            'planned_labour',
            'required_skilled_labour',
            'required_semiskilled_labour',
            'required_helpers',
            'materials_required',
            'machinery_required',
            'drawing_required',
            'client_approval_required',
            'responsible_person',
            'planned_date',
            'priority',
            'risks_constraints',
            'remarks',
        ]));

        return redirect()
            ->route('tomorrow-plans.index')
            ->with('success', 'Tomorrow plan updated successfully.');
    }

    public function submit(TomorrowPlan $tomorrowPlan)
    {
        if ($tomorrowPlan->status !== 'Draft') {
            return redirect()
                ->route('tomorrow-plans.index')
                ->with('error', 'Only draft tomorrow plans can be submitted.');
        }

        $tomorrowPlan->update(['status' => 'Submitted']);

        return redirect()
            ->route('tomorrow-plans.index')
            ->with('success', 'Tomorrow plan submitted successfully.');
    }

    public function approve(TomorrowPlan $tomorrowPlan)
    {
        if ($tomorrowPlan->status !== 'Submitted') {
            return redirect()
                ->route('tomorrow-plans.index')
                ->with('error', 'Only submitted tomorrow plans can be approved.');
        }

        if (! in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM'])) {
            abort(403);
        }

        $tomorrowPlan->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('tomorrow-plans.index')
            ->with('success', 'Tomorrow plan approved successfully.');
    }
}