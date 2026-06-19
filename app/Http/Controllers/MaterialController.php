<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\BrandMaster;
use App\Models\UnitMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with([
            'category',
            'brandMaster'
        ]);

        if ($request->filled('material_category_id')) {
            $query->where('material_category_id', $request->material_category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhereHas('brandMaster', function ($b) use ($search) {
                        $b->where('brand_name', 'like', "%{$search}%");
                    });
            });
        }

        $materials = $query
            ->orderBy('material_name')
            ->paginate(20)
            ->withQueryString();

        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('materials.index', compact(
            'materials',
            'categories'
        ));
    }

    public function create()
    {
        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        $brands = BrandMaster::where('is_active', true)
    ->orderBy('brand_name')
    ->get();

        $units = UnitMaster::where('is_active', true)
            ->orderBy('unit_name')
            ->get();

        return view('materials.create', compact(
            'categories',
            'brands',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_category_id' => 'nullable|exists:material_categories,id',
            'brand_master_id' => 'nullable|exists:brand_masters,id',
            'material_code' => 'nullable|string|max:255',
            'material_name' => 'required|string|max:255',
            'specification' => 'nullable|string|max:255',
            'unit' => 'required|string|max:255',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $material = Material::create([
            'material_category_id' => $request->material_category_id,
            'brand_master_id' => $request->brand_master_id,
            'material_code' => $request->material_code,
            'material_name' => $request->material_name,
            'specification' => $request->specification,
            'brand' => null,
            'unit' => $request->unit,
            'minimum_stock_level' => $request->minimum_stock_level ?? 0,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
            'Materials',
            'Created',
            'Material',
            $material->id,
            'Material created: ' . $material->material_name,
            null,
            $material->toArray()
        );

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material created successfully.');
    }

    public function edit(Material $material)
    {
        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        $brands = BrandMaster::where('is_active', true)
    ->orderBy('brand_name')
    ->get();

        $units = UnitMaster::where('is_active', true)
            ->orderBy('unit_name')
            ->get();

        return view('materials.edit', compact(
            'material',
            'categories',
            'brands',
            'units'
        ));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'material_category_id' => 'nullable|exists:material_categories,id',
            'brand_master_id' => 'nullable|exists:brand_masters,id',
            'material_code' => 'nullable|string|max:255',
            'material_name' => 'required|string|max:255',
            'specification' => 'nullable|string|max:255',
            'unit' => 'required|string|max:255',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $material->toArray();

        $material->update([
            'material_category_id' => $request->material_category_id,
            'brand_master_id' => $request->brand_master_id,
            'material_code' => $request->material_code,
            'material_name' => $request->material_name,
            'specification' => $request->specification,
            'brand' => null,
            'unit' => $request->unit,
            'minimum_stock_level' => $request->minimum_stock_level ?? 0,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
            'Materials',
            'Updated',
            'Material',
            $material->id,
            'Material updated: ' . $material->material_name,
            $oldValues,
            $material->fresh()->toArray()
        );

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material updated successfully.');
    }

    public function toggleStatus(Material $material)
    {
        $oldValues = $material->toArray();

        $material->update([
            'is_active' => !$material->is_active,
        ]);

        AuditHelper::log(
            'Materials',
            $material->is_active ? 'Activated' : 'Deactivated',
            'Material',
            $material->id,
            $material->is_active
                ? 'Material activated: ' . $material->material_name
                : 'Material deactivated: ' . $material->material_name,
            $oldValues,
            $material->fresh()->toArray()
        );

        return back()->with(
            'success',
            'Material status updated successfully.'
        );
    }
}