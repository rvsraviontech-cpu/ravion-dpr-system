<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Contractor;
use App\Models\LabourReport;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ActivityDivision;
use App\Helpers\AuditHelper;
use App\Models\LabourType;
use App\Models\LabourReportDetail;
use App\Models\LabourCategory;


class LabourReportController extends Controller
{
    public function index(Request $request)
{
    $query = LabourReport::with([
        'project',
        'block',
        'floor',
        'unit',
        'room',
        'subspace',
        'activity',
        'contractor',
    ]);

    if ($request->filled('project_id')) {
        $query->where('project_id', $request->project_id);
    }

    if ($request->filled('contractor_id')) {
        $query->where('contractor_id', $request->contractor_id);
    }

    if ($request->filled('activity_id')) {
        $query->where('activity_id', $request->activity_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('entry_date')) {
        $query->whereDate('entry_date', $request->entry_date);
    }

    $labourReports = $query->latest()->paginate(20)->withQueryString();

    $projects = Project::orderBy('project_name')->get();

    $contractors = Contractor::orderBy('contractor_name')->get();

    $activities = Activity::orderBy('activity_name')->get();

    $totalLabourToday = LabourReport::whereDate('entry_date', today())
        ->sum('total_labour');

    $submittedCount = LabourReport::where('status', 'Submitted')->count();

    $approvedCount = LabourReport::where('status', 'Approved')->count();

    $draftCount = LabourReport::where('status', 'Draft')->count();

    return view('labour-reports.index', compact(
        'labourReports',
        'projects',
        'contractors',
        'activities',
        'totalLabourToday',
        'submittedCount',
        'approvedCount',
        'draftCount'
    ));
}

    public function create()
    {
        $user = auth()->user();
        

if (in_array($user->role->name, ['Admin', 'PMO', 'DGM'])) {

    $projects = Project::where('status', 'Active')
        ->orderBy('project_name')
        ->get();

} else {

    $projects = $user->projects()
        ->where('status', 'Active')
        ->orderBy('project_name')
        ->get();


}

        $activities = Activity::where('is_active', 1)
            ->orderBy('activity_name')
            ->get();

        $contractors = Contractor::where('status', 1)
            ->orderBy('contractor_name')
            ->get();

            $projectBlocks = ProjectBlock::where('is_active', true)
    ->orderBy('name')
    ->get();

$projectFloors = ProjectFloor::where('is_active', true)
    ->orderBy('sequence')
    ->orderBy('name')
    ->get();

$projectUnits = ProjectUnit::where('is_active', true)
    ->orderBy('name')
    ->get();

$projectRooms = ProjectRoom::where('is_active', true)
    ->orderBy('name')
    ->get();

$projectSubspaces = ProjectSubspace::where('is_active', true)
    ->orderBy('name')
    ->get();

    $activityDivisions = ActivityDivision::where('is_active', true)
    ->orderBy('sequence')
    ->orderBy('name')
    ->get();
    $labourTypes = LabourType::orderBy('category')
    ->orderBy('labour_type_name')
    ->get()
    ->groupBy('category');

    $labourCategories = LabourCategory::where('is_active', true)
    ->orderBy('category_name')
    ->get();

$labourTypes = LabourType::with('category')
    ->orderBy('labour_type_name')
    ->get();

        return view('labour-reports.create', compact(
            'projects',
            'activities',
            'contractors',
            'projectBlocks',
            'projectFloors',
            'projectUnits',
            'projectRooms',
            'projectSubspaces',
            'activityDivisions',
            'labourTypes',
            'labourCategories',

        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required',
            'entry_date' => 'required|date',
        ]);

        $totalLabour =
            ($request->skilled_count ?? 0) +
            ($request->semi_skilled_count ?? 0) +
            ($request->helper_count ?? 0) +
            ($request->semi_helper_count ?? 0) +
            ($request->supervisor_count ?? 0) +
            ($request->technician_count ?? 0) +
            ($request->machine_operator_count ?? 0);

        $labourReport = LabourReport::create([
            'project_id' => $request->project_id,
            'user_id' => auth()->id(),

            'project_block_id' => $request->project_block_id,
            'project_floor_id' => $request->project_floor_id,
            'project_unit_id' => $request->project_unit_id,
            'project_room_id' => $request->project_room_id,
            'project_subspace_id' => $request->project_subspace_id,

            'activity_id' => $request->activity_id,
            'contractor_id' => $request->contractor_id,

            'skilled_count' => $request->skilled_count ?? 0,
            'semi_skilled_count' => $request->semi_skilled_count ?? 0,
            'helper_count' => $request->helper_count ?? 0,
            'semi_helper_count' => $request->semi_helper_count ?? 0,
            'supervisor_count' => $request->supervisor_count ?? 0,
            'technician_count' => $request->technician_count ?? 0,
            'machine_operator_count' => $request->machine_operator_count ?? 0,

            'male_count' => $request->male_count ?? 0,
            'female_count' => $request->female_count ?? 0,

            'local_count' => $request->local_count ?? 0,
            'non_local_count' => $request->non_local_count ?? 0,

            'total_labour' => $totalLabour,

            'shift' => $request->shift,
            'work_output' => $request->work_output,
            'work_output_unit' => $request->work_output_unit,

            'entry_date' => $request->entry_date,
            'entry_time' => now()->format('H:i:s'),

            'remarks' => $request->remarks,

            'status' => 'Draft',
        ]);

        $totalLabourFromDetails = 0;

if ($request->labour_type_id) {
    foreach ($request->labour_type_id as $index => $labourTypeId) {

        if (!$labourTypeId) {
            continue;
        }

        $male = $request->detail_male_count[$index] ?? 0;
        $female = $request->detail_female_count[$index] ?? 0;
        $local = $request->detail_local_count[$index] ?? 0;
        $nonLocal = $request->detail_non_local_count[$index] ?? 0;

        $total = $male + $female;

        $totalLabourFromDetails += $total;

        LabourReportDetail::create([
            'labour_report_id' => $labourReport->id,
            'labour_type_id' => $labourTypeId,
            'contractor_id' => $request->detail_contractor_id[$index] ?? null,
            'male_count' => $male,
            'female_count' => $female,
            'local_count' => $local,
            'non_local_count' => $nonLocal,
            'total_count' => $total,
            'remarks' => $request->detail_remarks[$index] ?? null,
        ]);
    }
}

if ($totalLabourFromDetails > 0) {
    $labourReport->update([
        'total_labour' => $totalLabourFromDetails,
        'male_count' => array_sum($request->detail_male_count ?? []),
        'female_count' => array_sum($request->detail_female_count ?? []),
        'local_count' => array_sum($request->detail_local_count ?? []),
        'non_local_count' => array_sum($request->detail_non_local_count ?? []),
    ]);
}
AuditHelper::log(
    'Labour Reports',
    'Created',
    'LabourReport',
    $labourReport->id,
    'Labour report created',
    null,
    $labourReport->only([
        'id',
        'project_id',
        'activity_id',
        'contractor_id',
        'total_labour',
        'entry_date',
        'status'
    ])
);
        return redirect()
            ->route('labour-reports.index')
            ->with('success', 'Labour report added successfully.');
    }

    public function edit(LabourReport $labourReport)
{
    
    if ($labourReport->status !== 'Draft') {
        abort(403, 'Only draft labour reports can be edited.');
    }

    if (in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM'])) {
    $user = auth()->user();

if (in_array($user->role->name, ['Admin', 'PMO', 'DGM'])) {
    $projects = Project::where('status', 'Active')
        ->orderBy('project_name')
        ->get();
} else {
    $projects = $user->projects()
        ->where('status', 'Active')
        ->orderBy('project_name')
        ->get();
}
} 



    $activities = Activity::where('is_active', 1)
        ->orderBy('activity_name')
        ->get();

    $contractors = Contractor::where('status', 1)
        ->orderBy('contractor_name')
        ->get();

    $projectBlocks = \App\Models\ProjectBlock::where('is_active', true)
        ->orderBy('name')
        ->get();

    $projectFloors = \App\Models\ProjectFloor::where('is_active', true)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    $projectUnits = \App\Models\ProjectUnit::where('is_active', true)
        ->orderBy('name')
        ->get();

    $projectRooms = \App\Models\ProjectRoom::where('is_active', true)
        ->orderBy('name')
        ->get();

    $projectSubspaces = \App\Models\ProjectSubspace::where('is_active', true)
        ->orderBy('name')
        ->get();

        $activityDivisions = ActivityDivision::where('is_active', true)
    ->orderBy('sequence')
    ->orderBy('name')
    ->get();

    return view('labour-reports.edit', compact(
        'labourReport',
        'projects',
        'activities',
        'contractors',
        'projectBlocks',
        'projectFloors',
        'projectUnits',
        'projectRooms',
        'projectSubspaces',
        'activityDivisions'
    ));
}

public function update(Request $request, LabourReport $labourReport)
{
    if ($labourReport->status !== 'Draft') {
        abort(403, 'Only draft labour reports can be updated.');
    }

    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'entry_date' => 'required|date',
    ]);

    $totalLabour =
        ($request->skilled_count ?? 0) +
        ($request->semi_skilled_count ?? 0) +
        ($request->helper_count ?? 0) +
        ($request->semi_helper_count ?? 0) +
        ($request->supervisor_count ?? 0) +
        ($request->technician_count ?? 0) +
        ($request->machine_operator_count ?? 0);


        $oldValues = $labourReport->only([
    'project_id',
    'activity_id',
    'contractor_id',
    'skilled_count',
    'semi_skilled_count',
    'helper_count',
    'semi_helper_count',
    'supervisor_count',
    'technician_count',
    'machine_operator_count',
    'male_count',
    'female_count',
    'local_count',
    'non_local_count',
    'total_labour',
    'shift',
    'work_output',
    'work_output_unit',
    'entry_date',
    'remarks',
    'status'
]);
    $labourReport->update([
        'project_id' => $request->project_id,

        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'project_room_id' => $request->project_room_id,
        'project_subspace_id' => $request->project_subspace_id,

        'activity_id' => $request->activity_id,
        'contractor_id' => $request->contractor_id,

        'skilled_count' => $request->skilled_count ?? 0,
        'semi_skilled_count' => $request->semi_skilled_count ?? 0,
        'helper_count' => $request->helper_count ?? 0,
        'semi_helper_count' => $request->semi_helper_count ?? 0,
        'supervisor_count' => $request->supervisor_count ?? 0,
        'technician_count' => $request->technician_count ?? 0,
        'machine_operator_count' => $request->machine_operator_count ?? 0,

        'male_count' => $request->male_count ?? 0,
        'female_count' => $request->female_count ?? 0,
        'local_count' => $request->local_count ?? 0,
        'non_local_count' => $request->non_local_count ?? 0,

        'total_labour' => $totalLabour,

        'shift' => $request->shift,
        'work_output' => $request->work_output,
        'work_output_unit' => $request->work_output_unit,

        'entry_date' => $request->entry_date,
        'remarks' => $request->remarks,
    ]);

    $newValues = $labourReport->only([
    'project_id',
    'activity_id',
    'contractor_id',
    'skilled_count',
    'semi_skilled_count',
    'helper_count',
    'semi_helper_count',
    'supervisor_count',
    'technician_count',
    'machine_operator_count',
    'male_count',
    'female_count',
    'local_count',
    'non_local_count',
    'total_labour',
    'shift',
    'work_output',
    'work_output_unit',
    'entry_date',
    'remarks',
    'status'
]);

AuditHelper::log(
    'Labour Reports',
    'Updated',
    'LabourReport',
    $labourReport->id,
    'Labour report updated',
    $oldValues,
    $newValues
);

    return redirect()
        ->route('labour-reports.index')
        ->with('success', 'Labour report updated successfully.');
}

public function submit(LabourReport $labourReport)
{
    if ($labourReport->status !== 'Draft') {
        return back()->with('error', 'Only draft labour reports can be submitted.');
    }

    $labourReport->update([
        'status' => 'Submitted',
    ]);

    AuditHelper::log(
    'Labour Reports',
    'Submitted',
    'LabourReport',
    $labourReport->id,
    'Labour report submitted for approval',
    ['status' => 'Draft'],
    ['status' => 'Submitted']
);

    return redirect()
        ->route('labour-reports.index')
        ->with('success', 'Labour report submitted successfully.');
}

public function approve(LabourReport $labourReport)
{
    if ($labourReport->status !== 'Submitted') {
        return back()->with('error', 'Only submitted labour reports can be approved.');
    }

    $labourReport->update([
        'status' => 'Approved',
    ]);

    AuditHelper::log(
    'Labour Reports',
    'Approved',
    'LabourReport',
    $labourReport->id,
    'Labour report approved',
    ['status' => 'Submitted'],
    ['status' => 'Approved']
);

    return redirect()
        ->route('labour-reports.index')
        ->with('success', 'Labour report approved successfully.');
}
public function show(LabourReport $labourReport)
{
    $labourReport->load([
        'project',
        'block',
        'floor',
        'unit',
        'room',
        'subspace',
        'activity',
        'activityMapping',
        'contractor',
        'engineer',
    ]);

    return view('labour-reports.show', compact(
        'labourReport'
    ));
}
}