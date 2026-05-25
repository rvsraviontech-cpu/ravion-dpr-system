<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\Activity;
use App\Models\Contractor;
use App\Models\Dpr;
use App\Models\DprWorkItem;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DprPhoto;
use App\Models\DprLabour;

class DprController extends Controller
{
    public function index(Request $request)
{
    $query = Dpr::with('project', 'user');

    // Engineer restriction

    if(auth()->user()->role->name == 'Engineer')
    {
        $query->where('user_id', auth()->id());
    }

    // Project filter

    if($request->project_id)
    {
        $query->where('project_id', $request->project_id);
    }

    // Status filter

    if($request->status)
    {
        $query->where('status', $request->status);
    }

    // Engineer filter

    if($request->user_id)
    {
        $query->where('user_id', $request->user_id);
    }
    if($request->from_date)
{
    $query->whereDate(
        'dpr_date',
        '>=',
        $request->from_date
    );
}

if($request->to_date)
{
    $query->whereDate(
        'dpr_date',
        '<=',
        $request->to_date
    );
}

    $dprs = $query->latest()->get();

    if(auth()->user()->role->name == 'Engineer')
{
    $projects = auth()->user()->projects;

    $engineers = \App\Models\User::where(
        'id',
        auth()->id()
    )->get();
}
else
{
    $projects = Project::all();

    $engineers = \App\Models\User::whereHas(
        'role',
        function($q){
            $q->where('name', 'Engineer');
        }
    )->get();
}

    return view('dprs.index', compact(
        'dprs',
        'projects',
        'engineers'
    ));
}

    public function create()
    {
        $projects = auth()->user()->projects;
        $activities = Activity::all();
        $contractors = Contractor::all();
        $labourTypes = \App\Models\LabourType::all();

        

        return view('dprs.create', compact(
            'projects',
            'activities',
            'contractors',
            'labourTypes'
        ));
    }

    public function store(Request $request)
    {
    
    $request->validate([

    'project_id' => 'required',

    'dpr_date' => 'required',

    'activity_id.*' => 'required',

    'contractor_id.*' => 'required',

    'quantity_completed.*' =>
        'required|numeric|min:0',

        'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',

]);
    

    $dpr = Dpr::create([
            'project_id' => $request->project_id,
            'user_id' => auth()->id(),
            'dpr_date' => $request->dpr_date,
            'weather' => $request->weather,
            'remarks' => $request->remarks,
            'status' => 'Pending',
        ]);

        foreach ($request->activity_id as $index => $activityId)
{
    DprWorkItem::create([

        'dpr_id' => $dpr->id,

        'activity_id' => $activityId,

        'contractor_id' => $request->contractor_id[$index],

        'quantity_completed' =>
            $request->quantity_completed[$index],

        'remarks' =>
            $request->work_remarks[$index],

    ]);
}

if($request->hasFile('photos'))
{
    foreach($request->file('photos') as $photo)
    {
        $path = $photo->store(
            'dpr_photos',
            'public'
        );

        DprPhoto::create([
            'dpr_id' => $dpr->id,
            'photo_path' => $path
        ]);
    }
}
if($request->labour_type)
{
    foreach($request->labour_type as $index => $labourTypeId)
    {
        if(!$labourTypeId)
        {
            continue;
        }

        $male =
            $request->male_count[$index] ?? 0;

        $female =
            $request->female_count[$index] ?? 0;

        $local =
            $request->local_count[$index] ?? 0;

        $nonLocal =
            $request->non_local_count[$index] ?? 0;

        DprLabour::create([

            'dpr_id' => $dpr->id,

            'labour_type_id' => $labourTypeId,

            'male_count' => $male,

            'female_count' => $female,

            'local_count' => $local,

            'non_local_count' => $nonLocal,

            'total_count' =>
                $male + $female

        ]);
    }
}

        return redirect('/dprs')
    ->with('success', 'DPR submitted successfully.');
    }

  
public function pmoQueue()
{
    $dprs = Dpr::with('project', 'user')
        ->where('status', 'Pending')
        ->latest()
        ->get();

    return view('dprs.pmo-queue', compact('dprs'));
}

public function approve(Request $request, $id)
{
    $dpr = Dpr::findOrFail($id);

    $dpr->status = 'Approved';

    $dpr->pmo_remarks = $request->pmo_remarks;

    $dpr->save();

    return redirect('/pmo/dprs')
        ->with('success', 'DPR approved successfully.');
}

public function reject(Request $request, $id)
{
    $dpr = Dpr::findOrFail($id);

    $dpr->status = 'Rejected';

    $dpr->pmo_remarks = $request->pmo_remarks;

    $dpr->save();

    return redirect('/pmo/dprs')
        ->with('success', 'DPR rejected successfully.');
}

public function show($id)
{
    $dpr = \App\Models\Dpr::with([
        'project',
        'user',
        'workItems.activity',
        'workItems.contractor',
        'photos',
        'labours.labourType'
    ])->findOrFail($id);

    return view('dprs.show', compact('dpr'));
}
public function edit($id)
{
    $dpr = Dpr::with('workItems')->findOrFail($id);

    if($dpr->status == 'Approved')
    {
        return redirect('/dprs')
            ->with('success', 'Approved DPR cannot be edited.');
    }

    $projects = Project::all();

    $activities = Activity::all();

    $contractors = Contractor::all();

    return view('dprs.edit', compact(
        'dpr',
        'projects',
        'activities',
        'contractors'
    ));
}

public function update(Request $request, $id)
{
    $dpr = Dpr::findOrFail($id);

    if($dpr->status == 'Approved')
    {
        return redirect('/dprs')
            ->with('success', 'Approved DPR cannot be updated.');
    }

    $dpr->update([
        'project_id' => $request->project_id,
        'dpr_date' => $request->dpr_date,
        'weather' => $request->weather,
        'remarks' => $request->remarks,
    ]);

    return redirect('/dprs')
        ->with('success', 'DPR updated successfully.');
}

public function destroy($id)
{
    $dpr = Dpr::findOrFail($id);

    if($dpr->status == 'Approved')
    {
        return redirect('/dprs')
            ->with('success', 'Approved DPR cannot be deleted.');
    }

    $dpr->delete();

    return redirect('/dprs')
        ->with('success', 'DPR deleted successfully.');
}
public function downloadPdf($id)
{
    $dpr = Dpr::with([
        'project',
        'user',
        'workItems.activity',
        'workItems.contractor',
        'photos',
        'labours.labourType'
    ])->findOrFail($id);

    $pdf = Pdf::loadView('dprs.pdf', compact('dpr'));

    $fileName =
    $date =
    \Carbon\Carbon::parse($dpr->dpr_date)
        ->format('d-m-Y');

$fileName =
    $date . '_' .
    str_replace(' ', '-', $dpr->project->project_name) . '_' .
    str_replace(' ', '-', $dpr->user->name) .
    '.pdf';

return $pdf->download($fileName);
}
}