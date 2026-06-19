<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contractor;
use App\Helpers\AuditHelper;

class ContractorController extends Controller
{
    public function index()
    {
        $contractors = Contractor::all();

        return view('contractors.index', compact('contractors'));
    }

    public function create()
    {
        return view('contractors.create');
    }

    public function store(Request $request)
    {
        $contractor = Contractor::create([
            'contractor_name' => $request->contractor_name,
            'mobile' => $request->mobile,
            'work_category' => $request->work_category,
            'status' => 'Active',
        ]);

        AuditHelper::log(
    'Contractors',
    'Created',
    'Contractor',
    $contractor->id,
    'Contractor created: ' . $contractor->contractor_name,
    null,
    $contractor->only([
        'id',
        'contractor_name',
        'mobile',
        'work_category',
        'status'
    ])
);

        return redirect('/contractors')
    ->with('success', 'Contractor created successfully.');
    }
    public function edit($id)
{
    $contractor = Contractor::findOrFail($id);

    return view('contractors.edit', compact('contractor'));
}

public function update(Request $request, $id)
{
    $contractor = Contractor::findOrFail($id);

    $oldValues = $contractor->only([
    'contractor_name',
    'mobile',
    'work_category',
    'status'
]);

$contractor->update($request->only([
    'contractor_name',
    'mobile',
    'work_category',
    'status'
]));

$newValues = $contractor->only([
    'contractor_name',
    'mobile',
    'work_category',
    'status'
]);

$action = 'Updated';
$description = 'Contractor updated: ' . $contractor->contractor_name;

if (($oldValues['status'] ?? null) !== ($newValues['status'] ?? null)) {
    $action = $newValues['status'];
    $description =
        'Contractor status changed from ' .
        $oldValues['status'] .
        ' to ' .
        $newValues['status'];
}

AuditHelper::log(
    'Contractors',
    $action,
    'Contractor',
    $contractor->id,
    $description,
    $oldValues,
    $newValues
);

    $contractor->update($request->all());

    return redirect('/contractors')
        ->with('success', 'Contractor updated successfully.');
}

public function destroy($id)
{
    $contractor = Contractor::findOrFail($id);
    AuditHelper::log(
    'Contractors',
    'Deleted',
    'Contractor',
    $contractor->id,
    'Contractor deleted: ' . $contractor->contractor_name,
    $contractor->only([
        'id',
        'contractor_name',
        'mobile',
        'work_category',
        'status'
    ]),
    null
);

    $contractor->delete();

    return redirect('/contractors')
        ->with('success', 'Contractor deleted successfully.');
}
}