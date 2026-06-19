<?php

namespace App\Http\Controllers;

use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

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

        $materialCategory = MaterialCategory::create([
            'category_name' => $request->category_name,
            'category_code' => $request->category_code,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Material Categories',
    'Created',
    'MaterialCategory',
    $materialCategory->id,
    'Material category created: ' . $materialCategory->category_name,
    null,
    $materialCategory->only([
        'id',
        'category_name',
        'category_code',
        'is_active',
        'remarks'
    ])
);



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

        $oldValues = $materialCategory->only([
    'category_name',
    'category_code',
    'is_active',
    'remarks'
]);

        $materialCategory->update([
            'category_name' => $request->category_name,
            'category_code' => $request->category_code,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        $newValues = $materialCategory->only([
    'category_name',
    'category_code',
    'is_active',
    'remarks'
]);

$action = 'Updated';
$description =
    'Material category updated: ' .
    $materialCategory->category_name;

if (
    ($oldValues['is_active'] ?? null) !=
    ($newValues['is_active'] ?? null)
) {
    $action = $newValues['is_active']
        ? 'Activated'
        : 'Deactivated';

    $description = $newValues['is_active']
        ? 'Material category activated: ' . $materialCategory->category_name
        : 'Material category deactivated: ' . $materialCategory->category_name;
}

AuditHelper::log(
    'Material Categories',
    $action,
    'MaterialCategory',
    $materialCategory->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('material-categories.index')
            ->with('success', 'Material category updated successfully.');
    }

    public function toggleStatus(MaterialCategory $materialCategory)
{
    $oldValues = $materialCategory->only([
        'category_name',
        'category_code',
        'is_active',
        'remarks'
    ]);

    $materialCategory->update([
        'is_active' => !$materialCategory->is_active,
    ]);

    $newValues = $materialCategory->only([
        'category_name',
        'category_code',
        'is_active',
        'remarks'
    ]);

    AuditHelper::log(
        'Material Categories',
        $materialCategory->is_active ? 'Activated' : 'Deactivated',
        'MaterialCategory',
        $materialCategory->id,
        $materialCategory->is_active
            ? 'Material category activated: ' . $materialCategory->category_name
            : 'Material category deactivated: ' . $materialCategory->category_name,
        $oldValues,
        $newValues
    );

    return back()->with(
        'success',
        'Material category status updated successfully.'
    );
}
}