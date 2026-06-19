<?php

namespace App\Http\Controllers;

use App\Models\SiteIssue;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\Activity;
use App\Models\ActivityDivision;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class SiteIssueController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteIssue::with([
            'project',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'activity',
            'creator',
        ])->latest();

        if ($request->filled('issue_date')) {
            $query->whereDate('issue_date', $request->issue_date);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $siteIssues = $query->paginate(10);

        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        return view('site-issues.index', compact(
            'siteIssues',
            'projects'
        ));
    }

    public function create()
    {
        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
        $projectFloors = ProjectFloor::where('is_active', true)->orderBy('name')->get();
        $projectUnits = ProjectUnit::where('is_active', true)->orderBy('name')->get();
        $projectRooms = ProjectRoom::orderBy('name')->get();
        $projectSubspaces = ProjectSubspace::where('is_active', true)->orderBy('name')->get();
        $activityDivisions = ActivityDivision::where('is_active', 1)->orderBy('name')->get();
        $activities = Activity::where('is_active', 1)->orderBy('activity_name')->get();

        return view('site-issues.create', compact(
            'projects',
            'projectBlocks',
            'projectFloors',
            'projectUnits',
            'projectRooms',
            'projectSubspaces',
            'activityDivisions',
            'activities'
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
            'activity_id' => 'nullable|exists:activities,id',
            'issue_date' => 'required|date',
            'issue_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'root_cause' => 'nullable|string|max:255',
            'responsible_person' => 'nullable|string|max:255',
            'target_closure_date' => 'nullable|date',
            'priority' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'escalated_to_pmo' => 'nullable|boolean',
            'escalated_to_management' => 'nullable|boolean',
            'remarks' => 'nullable|string',
        ]);

        $siteIssue = SiteIssue::create([
            'project_id' => $request->project_id,
            'project_block_id' => $request->project_block_id,
            'project_floor_id' => $request->project_floor_id,
            'project_unit_id' => $request->project_unit_id,
            'project_room_id' => $request->project_room_id,
            'project_subspace_id' => $request->project_subspace_id,
            'activity_id' => $request->activity_id,
            'issue_date' => $request->issue_date,
            'issue_type' => $request->issue_type,
            'title' => $request->title,
            'description' => $request->description,
            'root_cause' => $request->root_cause,
            'responsible_person' => $request->responsible_person,
            'target_closure_date' => $request->target_closure_date,
            'priority' => $request->priority,
            'status' => $request->status,
            'escalated_to_pmo' => $request->boolean('escalated_to_pmo'),
            'escalated_to_management' => $request->boolean('escalated_to_management'),
            'created_by' => auth()->id(),
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Site Issues',
    'Created',
    'SiteIssue',
    $siteIssue->id,
    'Site issue created',
    null,
    $siteIssue->only([
        'id',
        'project_id',
        'activity_id',
        'issue_date',
        'issue_type',
        'title',
        'priority',
        'status',
        'responsible_person',
        'target_closure_date',
        'created_by'
    ])
);

        return redirect()
            ->route('site-issues.index')
            ->with('success', 'Site issue created successfully.');
    }

    public function show(SiteIssue $siteIssue)
    {
        $siteIssue->load([
            'project',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'activity',
            'creator',
        ]);

        return view('site-issues.show', compact('siteIssue'));
    }

    public function edit(SiteIssue $siteIssue)
    {
        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
        $projectFloors = ProjectFloor::where('is_active', true)->orderBy('name')->get();
        $projectUnits = ProjectUnit::where('is_active', true)->orderBy('name')->get();
        $projectRooms = ProjectRoom::orderBy('name')->get();
        $projectSubspaces = ProjectSubspace::where('is_active', true)->orderBy('name')->get();
        $activityDivisions = ActivityDivision::where('is_active', 1)->orderBy('name')->get();
        $activities = Activity::where('is_active', 1)->orderBy('activity_name')->get();

        return view('site-issues.edit', compact(
            'siteIssue',
            'projects',
            'projectBlocks',
            'projectFloors',
            'projectUnits',
            'projectRooms',
            'projectSubspaces',
            'activityDivisions',
            'activities'
        ));
    }

    public function update(Request $request, SiteIssue $siteIssue)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_block_id' => 'nullable|exists:project_blocks,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'project_room_id' => 'nullable|exists:project_rooms,id',
            'project_subspace_id' => 'nullable|exists:project_subspaces,id',
            'activity_id' => 'nullable|exists:activities,id',
            'issue_date' => 'required|date',
            'issue_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'root_cause' => 'nullable|string|max:255',
            'responsible_person' => 'nullable|string|max:255',
            'target_closure_date' => 'nullable|date',
            'actual_closure_date' => 'nullable|date',
            'priority' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'escalated_to_pmo' => 'nullable|boolean',
            'escalated_to_management' => 'nullable|boolean',
            'resolution' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $siteIssue->only([
    'project_id',
    'project_block_id',
    'project_floor_id',
    'project_unit_id',
    'project_room_id',
    'project_subspace_id',
    'activity_id',
    'issue_date',
    'issue_type',
    'title',
    'description',
    'root_cause',
    'responsible_person',
    'target_closure_date',
    'actual_closure_date',
    'priority',
    'status',
    'escalated_to_pmo',
    'escalated_to_management',
    'resolution',
    'remarks'
]);

        $siteIssue->update([
            'project_id' => $request->project_id,
            'project_block_id' => $request->project_block_id,
            'project_floor_id' => $request->project_floor_id,
            'project_unit_id' => $request->project_unit_id,
            'project_room_id' => $request->project_room_id,
            'project_subspace_id' => $request->project_subspace_id,
            'activity_id' => $request->activity_id,
            'issue_date' => $request->issue_date,
            'issue_type' => $request->issue_type,
            'title' => $request->title,
            'description' => $request->description,
            'root_cause' => $request->root_cause,
            'responsible_person' => $request->responsible_person,
            'target_closure_date' => $request->target_closure_date,
            'actual_closure_date' => $request->actual_closure_date,
            'priority' => $request->priority,
            'status' => $request->status,
            'escalated_to_pmo' => $request->boolean('escalated_to_pmo'),
            'escalated_to_management' => $request->boolean('escalated_to_management'),
            'resolution' => $request->resolution,
            'remarks' => $request->remarks,
        ]);

        $newValues = $siteIssue->only([
    'project_id',
    'project_block_id',
    'project_floor_id',
    'project_unit_id',
    'project_room_id',
    'project_subspace_id',
    'activity_id',
    'issue_date',
    'issue_type',
    'title',
    'description',
    'root_cause',
    'responsible_person',
    'target_closure_date',
    'actual_closure_date',
    'priority',
    'status',
    'escalated_to_pmo',
    'escalated_to_management',
    'resolution',
    'remarks'
]);

$action = 'Updated';
$description = 'Site issue updated';

$oldStatus = $oldValues['status'] ?? null;
$newStatus = $newValues['status'] ?? null;

if ($oldStatus !== $newStatus)
{
    $action = $newStatus;

    $description =
        'Site issue status changed from ' .
        $oldStatus .
        ' to ' .
        $newStatus;
}

AuditHelper::log(
    'Site Issues',
    $action,
    'SiteIssue',
    $siteIssue->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('site-issues.index')
            ->with('success', 'Site issue updated successfully.');
    }
}