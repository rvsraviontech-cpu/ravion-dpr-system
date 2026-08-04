<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineryTool;
use App\Helpers\AuditHelper;

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

        $machineryTool = MachineryTool::create(
    $request->all()
);

AuditHelper::log(
    'Machinery Tools',
    'Created',
    'MachineryTool',
    $machineryTool->id,
    'Machinery/tool created: ' . $machineryTool->machine_name,
    null,
    $machineryTool->toArray()
);
       

        return redirect('/machinery-tools')
            ->with(
                'success',
                'Machinery created successfully.'
            );
    }
}