<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialReceived;
use App\Models\MaterialVerification;

class MaterialVerificationController extends Controller
{
    public function index()
    {
        $materialReceiveds = MaterialReceived::with([
            'project',
            'block',
            'materialCategory',
            'material',
            'engineer'
        ])
        ->latest()
        ->paginate(10);

        return view('material-verifications.index', compact('materialReceiveds'));
    }

    public function show(MaterialReceived $materialReceived)
    {
        $materialReceived->load([
            'project',
            'block',
            'floor',
            'unit',
            'materialCategory',
            'material',
            'contractor',
            'engineer'
        ]);

        return view('material-verifications.show', compact('materialReceived'));
    }

    public function verify(Request $request, MaterialReceived $materialReceived)
    {
        $request->validate([
            'verification_status' => 'required|in:Verified,Rejected,Hold',
            'accepted_quantity' => 'required|numeric|min:0',
            'short_quantity' => 'nullable|numeric|min:0',
            'damaged_quantity' => 'nullable|numeric|min:0',
            'rejected_quantity' => 'nullable|numeric|min:0',
            'verification_remarks' => 'nullable|string',
        ]);

        MaterialVerification::updateOrCreate(
            [
                'material_received_id' => $materialReceived->id,
            ],
            [
                'project_id' => $materialReceived->project_id,
                'project_block_id' => $materialReceived->project_block_id,
                'material_category_id' => $materialReceived->material_category_id,
                'material_id' => $materialReceived->material_id,

                'received_quantity' => $materialReceived->quantity_received,
                'accepted_quantity' => $request->accepted_quantity,
                'short_quantity' => $request->short_quantity ?? 0,
                'damaged_quantity' => $request->damaged_quantity ?? 0,
                'rejected_quantity' => $request->rejected_quantity ?? 0,

                'unit' => $materialReceived->unit,

                'verification_status' => $request->verification_status,
                'verified_by' => auth()->id(),
                'verified_at' => now(),

                'verification_remarks' => $request->verification_remarks,
            ]
        );

        $materialReceived->update([
            'pmo_verification_status' => $request->verification_status,
            'accepted_quantity' => $request->accepted_quantity,
            'short_quantity' => $request->short_quantity ?? 0,
            'damaged_quantity' => $request->damaged_quantity ?? 0,
            'rejected_quantity' => $request->rejected_quantity ?? 0,
        ]);

        return redirect()
            ->route('material-verifications.index')
            ->with('success', 'Material verification updated successfully.');
    }
}