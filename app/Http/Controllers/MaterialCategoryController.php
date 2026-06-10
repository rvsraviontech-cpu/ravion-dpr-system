<?php

namespace App\Http\Controllers;

use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{
    public function index()
    {
        $categories = MaterialCategory::orderBy('category_name')->paginate(20);

        return view('material-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('material-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:material_categories,category_name',
            'category_code' => 'nullable|string|max:255|unique:material_categories,category_code',
            'remarks' => 'nullable|string',
        ]);

        MaterialCategory::create([
            'category_name' => $request->category_name,
            'category_code' => $request->category_code,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('material-categories.index')
            ->with('success', 'Material category added successfully.');
    }

    public function edit(MaterialCategory $materialCategory)
    {
        return view('material-categories.edit', compact('materialCategory'));
    }

    public function update(Request $request, MaterialCategory $materialCategory)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:material_categories,category_name,' . $materialCategory->id,
            'category_code' => 'nullable|string|max:255|unique:material_categories,category_code,' . $materialCategory->id,
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $materialCategory->update([
            'category_name' => $request->category_name,
            'category_code' => $request->category_code,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('material-categories.index')
            ->with('success', 'Material category updated successfully.');
    }
}