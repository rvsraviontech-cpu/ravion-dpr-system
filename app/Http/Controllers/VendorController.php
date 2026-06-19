<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialCategory;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::with('category');

        if ($request->filled('material_category_id')) {
            $query->where('material_category_id', $request->material_category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                    ->orWhere('vendor_code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        $vendors = $query
            ->orderBy('vendor_name')
            ->paginate(20)
            ->withQueryString();

        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('vendors.index', compact(
            'vendors',
            'categories'
        ));
    }

    public function create()
    {
        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('vendors.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_code' => 'nullable|string|max:255|unique:vendors,vendor_code',
            'material_category_id' => 'nullable|exists:material_categories,id',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'alternate_mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'credit_days' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $vendor = Vendor::create([
            'vendor_code' => $request->vendor_code,
            'material_category_id' => $request->material_category_id,
            'vendor_name' => $request->vendor_name,
            'contact_person' => $request->contact_person,
            'mobile' => $request->mobile,
            'alternate_mobile' => $request->alternate_mobile,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'payment_terms' => $request->payment_terms,
            'credit_days' => $request->credit_days ?? 0,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
            'Vendors',
            'Created',
            'Vendor',
            $vendor->id,
            'Vendor created: ' . $vendor->vendor_name,
            null,
            $vendor->toArray()
        );

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        $categories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('vendors.edit', compact(
            'vendor',
            'categories'
        ));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_code' => 'nullable|string|max:255|unique:vendors,vendor_code,' . $vendor->id,
            'material_category_id' => 'nullable|exists:material_categories,id',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'alternate_mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'credit_days' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $vendor->toArray();

        $vendor->update([
            'vendor_code' => $request->vendor_code,
            'material_category_id' => $request->material_category_id,
            'vendor_name' => $request->vendor_name,
            'contact_person' => $request->contact_person,
            'mobile' => $request->mobile,
            'alternate_mobile' => $request->alternate_mobile,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'payment_terms' => $request->payment_terms,
            'credit_days' => $request->credit_days ?? 0,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
            'Vendors',
            'Updated',
            'Vendor',
            $vendor->id,
            'Vendor updated: ' . $vendor->vendor_name,
            $oldValues,
            $vendor->fresh()->toArray()
        );

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function toggleStatus(Vendor $vendor)
    {
        $oldValues = $vendor->toArray();

        $vendor->update([
            'is_active' => !$vendor->is_active,
        ]);

        AuditHelper::log(
            'Vendors',
            $vendor->is_active ? 'Activated' : 'Deactivated',
            'Vendor',
            $vendor->id,
            $vendor->is_active
                ? 'Vendor activated: ' . $vendor->vendor_name
                : 'Vendor deactivated: ' . $vendor->vendor_name,
            $oldValues,
            $vendor->fresh()->toArray()
        );

        return back()->with('success', 'Vendor status updated successfully.');
    }
}