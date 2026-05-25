<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineryTool;

class MachineryToolController extends Controller
{
    public function index()
    {
        $machineries =
            MachineryTool::latest()->get();

        return view(
            'machinery-tools.index',
            compact('machineries')
        );
    }

    public function create()
    {
        return view(
            'machinery-tools.create'
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'machine_name' => 'required'

        ]);

        MachineryTool::create(
            $request->all()
        );

        return redirect('/machinery-tools')
            ->with(
                'success',
                'Machinery created successfully.'
            );
    }
}