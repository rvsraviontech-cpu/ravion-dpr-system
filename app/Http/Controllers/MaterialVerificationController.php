<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialReceived;
use App\Models\MaterialVerification;
use App\Helpers\AuditHelper;

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

        $oldValues = [
    'pmo_verification_status' => $materialReceived->pmo_verification_status,
    'accepted_quantity' => $materialReceived->accepted_quantity,
    'short_quantity' => $materialReceived->short_quantity,
    'damaged_quantity' => $materialReceived->damaged_quantity,
    'rejected_quantity' => $materialReceived->rejected_quantity,
];

        $materialVerification = MaterialVerification::updateOrCreate(
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

        $newValues = [
    'pmo_verification_status' => $materialReceived->pmo_verification_status,
    'accepted_quantity' => $materialReceived->accepted_quantity,
    'short_quantity' => $materialReceived->short_quantity,
    'damaged_quantity' => $materialReceived->damaged_quantity,
    'rejected_quantity' => $materialReceived->rejected_quantity,
    'verification_remarks' => $request->verification_remarks,
];

AuditHelper::log(
    'Material Verification',
    $request->verification_status,
    'MaterialVerification',
    $materialVerification->id,
    'Material verification status changed to ' . $request->verification_status,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('material-verifications.index')
            ->with('success', 'Material verification updated successfully.');
    }
}