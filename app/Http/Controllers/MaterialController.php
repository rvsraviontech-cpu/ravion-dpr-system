<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::latest()->get();

        return view(
            'materials.index',
            compact('materials')
        );
    }

    public function create()
    {
        return view('materials.create');
    }

    public function store(Request $request)
    {
        $request->validate([

            'material_name' => 'required',

            'unit' => 'required'

        ]);

        Material::create($request->all());

        return redirect('/materials')
            ->with(
                'success',
                'Material created successfully.'
            );
    }
}