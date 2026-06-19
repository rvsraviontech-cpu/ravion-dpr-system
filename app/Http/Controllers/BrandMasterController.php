<?php

namespace App\Http\Controllers;

use App\Models\BrandMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;
use App\Models\MaterialCategory;

class BrandMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = BrandMaster::with('category');
        if ($request->filled('material_category_id')) {
    $query->where('material_category_id', $request->material_category_id);
}

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                  ->orWhere('brand_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $brands = $query
            ->orderBy('brand_name')
            ->paginate(20)
            ->withQueryString();

            $categories = MaterialCategory::where('is_active', true)
    ->orderBy('category_name')
    ->get();

        return view('brand-masters.index', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255|unique:brand_masters,brand_name',
            'brand_code' => 'nullable|string|max:255|unique:brand_masters,brand_code',
            'remarks' => 'nullable|string',
            'material_category_id' => 'required|exists:material_categories,id',
        ]);

            $brand = BrandMaster::create([
            'brand_name' => $request->brand_name,
            'brand_code' => $request->brand_code,
            'is_active' => true,
            'remarks' => $request->remarks,
            'material_category_id' => $request->material_category_id,
        ]);

        AuditHelper::log(
            'Brand Masters',
            'Created',
            'BrandMaster',
            $brand->id,
            'Brand created: ' . $brand->brand_name,
            null,
            $brand->toArray()
        );

        return redirect()
            ->route('brand-masters.index')
            ->with('success', 'Brand added successfully.');
    }

    public function edit(BrandMaster $brandMaster)
    {
        $categories = MaterialCategory::where('is_active', true)
    ->orderBy('category_name')
    ->get();

return view('brand-masters.edit', compact('brandMaster', 'categories'));
    }

    public function update(Request $request, BrandMaster $brandMaster)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255|unique:brand_masters,brand_name,' . $brandMaster->id,
            'brand_code' => 'nullable|string|max:255|unique:brand_masters,brand_code,' . $brandMaster->id,
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
            'material_category_id' => 'required|exists:material_categories,id',
        ]);

        $oldValues = $brandMaster->toArray();

        $brandMaster->update([
            'brand_name' => $request->brand_name,
            'brand_code' => $request->brand_code,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
            'material_category_id' => $request->material_category_id,
        ]);

        AuditHelper::log(
            'Brand Masters',
            'Updated',
            'BrandMaster',
            $brandMaster->id,
            'Brand updated: ' . $brandMaster->brand_name,
            $oldValues,
            $brandMaster->fresh()->toArray()
        );

        return redirect()
            ->route('brand-masters.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function toggleStatus(BrandMaster $brandMaster)
    {
        $oldValues = $brandMaster->toArray();

        $brandMaster->update([
            'is_active' => !$brandMaster->is_active,
        ]);

        AuditHelper::log(
            'Brand Masters',
            $brandMaster->is_active ? 'Activated' : 'Deactivated',
            'BrandMaster',
            $brandMaster->id,
            $brandMaster->is_active
                ? 'Brand activated: ' . $brandMaster->brand_name
                : 'Brand deactivated: ' . $brandMaster->brand_name,
            $oldValues,
            $brandMaster->fresh()->toArray()
        );

        return back()->with('success', 'Brand status updated successfully.');
    }
    
    public function create()
{
    $categories = MaterialCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    return view('brand-masters.create', compact('categories'));
}
}