<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabourType;

class LabourTypeController extends Controller
{
    public function index()
    {
        $labourTypes = LabourType::latest()->get();

        return view(
            'labour-types.index',
            compact('labourTypes')
        );
    }

    public function create()
    {
        return view('labour-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'labour_type_name' => 'required'
        ]);

        LabourType::create($request->all());

        return redirect('/labour-types')
            ->with('success', 'Labour Type created successfully.');
    }
}