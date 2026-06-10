<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::with('category')
            ->orderBy('material_name')
            ->paginate(20);

        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('materials.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_category_id' => 'nullable|exists:material_categories,id',
            'material_code' => 'nullable|string|max:255',
            'material_name' => 'required|string|max:255',
            'specification' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'unit' => 'required|string|max:255',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        Material::create([
            'material_category_id' => $request->material_category_id,
            'material_code' => $request->material_code,
            'material_name' => $request->material_name,
            'specification' => $request->specification,
            'brand' => $request->brand,
            'unit' => $request->unit,
            'minimum_stock_level' => $request->minimum_stock_level ?? 0,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material created successfully.');
    }

    public function edit(Material $material)
    {
        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('materials.edit', compact('material', 'categories'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'material_category_id' => 'nullable|exists:material_categories,id',
            'material_code' => 'nullable|string|max:255',
            'material_name' => 'required|string|max:255',
            'specification' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'unit' => 'required|string|max:255',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $material->update([
            'material_category_id' => $request->material_category_id,
            'material_code' => $request->material_code,
            'material_name' => $request->material_name,
            'specification' => $request->specification,
            'brand' => $request->brand,
            'unit' => $request->unit,
            'minimum_stock_level' => $request->minimum_stock_level ?? 0,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material updated successfully.');
    }
}