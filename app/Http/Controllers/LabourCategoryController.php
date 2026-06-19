<?php

namespace App\Http\Controllers;

use App\Models\LabourCategory;
use Illuminate\Http\Request;

class LabourCategoryController extends Controller
{
    public function index()
    {
        $categories = LabourCategory::withCount('labourTypes')
            ->orderBy('category_name')
            ->paginate(20);

        return view('labour-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:labour_categories,category_name',
            'remarks' => 'nullable|string',
        ]);

        LabourCategory::create([
            'category_name' => $request->category_name,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('labour-categories.index')
            ->with('success', 'Labour category added successfully.');
    }

    public function edit(LabourCategory $labourCategory)
    {
        return view('labour-categories.edit', compact('labourCategory'));
    }

    public function update(Request $request, LabourCategory $labourCategory)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:labour_categories,category_name,' . $labourCategory->id,
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $labourCategory->update([
            'category_name' => $request->category_name,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('labour-categories.index')
            ->with('success', 'Labour category updated successfully.');
    }

    public function toggleStatus(LabourCategory $labourCategory)
    {
        $labourCategory->update([
            'is_active' => !$labourCategory->is_active,
        ]);

        return back()->with('success', 'Labour category status updated successfully.');
    }
}