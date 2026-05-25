<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contractor;

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
        Contractor::create([
            'contractor_name' => $request->contractor_name,
            'mobile' => $request->mobile,
            'work_category' => $request->work_category,
            'status' => 'Active',
        ]);

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

    $contractor->update($request->all());

    return redirect('/contractors')
        ->with('success', 'Contractor updated successfully.');
}

public function destroy($id)
{
    $contractor = Contractor::findOrFail($id);

    $contractor->delete();

    return redirect('/contractors')
        ->with('success', 'Contractor deleted successfully.');
}
}