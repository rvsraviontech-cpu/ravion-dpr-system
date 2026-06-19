<?php

namespace App\Http\Controllers;

use App\Models\LabourCategory;
use App\Models\LabourType;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class LabourTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = LabourType::with('labourCategory');

        if ($request->filled('labour_category_id')) {
            $query->where('labour_category_id', $request->labour_category_id);
        }

        if ($request->filled('search')) {
            $query->where('labour_type_name', 'like', '%' . $request->search . '%');
        }

        $labourTypes = $query
            ->orderBy('labour_category_id')
            ->orderBy('labour_type_name')
            ->paginate(20)
            ->withQueryString();

        $labourCategories = LabourCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('labour-types.index', compact(
            'labourTypes',
            'labourCategories'
        ));
    }

    public function create()
    {
        $labourCategories = LabourCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('labour-types.create', compact('labourCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'labour_category_id' => 'required|exists:labour_categories,id',
            'labour_type_name' => 'required|string|max:255|unique:labour_types,labour_type_name',
        ]);

        $labourType = LabourType::create([
            'labour_category_id' => $request->labour_category_id,
            'labour_type_name' => $request->labour_type_name,
        ]);

        AuditHelper::log(
            'Labour Types',
            'Created',
            'LabourType',
            $labourType->id,
            'Labour type created: ' . $labourType->labour_type_name,
            null,
            $labourType->toArray()
        );

        return redirect()
            ->route('labour-types.index')
            ->with('success', 'Labour Type created successfully.');
    }

    public function edit(LabourType $labourType)
    {
        $labourCategories = LabourCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('labour-types.edit', compact(
            'labourType',
            'labourCategories'
        ));
    }

    public function update(Request $request, LabourType $labourType)
    {
        $request->validate([
            'labour_category_id' => 'required|exists:labour_categories,id',
            'labour_type_name' => 'required|string|max:255|unique:labour_types,labour_type_name,' . $labourType->id,
        ]);

        $oldValues = $labourType->toArray();

        $labourType->update([
            'labour_category_id' => $request->labour_category_id,
            'labour_type_name' => $request->labour_type_name,
        ]);

        AuditHelper::log(
            'Labour Types',
            'Updated',
            'LabourType',
            $labourType->id,
            'Labour type updated: ' . $labourType->labour_type_name,
            $oldValues,
            $labourType->fresh()->toArray()
        );

        return redirect()
            ->route('labour-types.index')
            ->with('success', 'Labour Type updated successfully.');
    }

    public function toggle(LabourType $labourType)
{
    $old = $labourType->status;

    $labourType->update([
        'status' => !$labourType->status,
    ]);

    AuditHelper::log(
        'Labour Types',
        'Status Changed',
        'LabourType',
        $labourType->id,
        'Labour type status changed',
        ['status' => $old],
        ['status' => $labourType->status]
    );

    return back()->with(
        'success',
        'Labour type status updated successfully.'
    );
}
}