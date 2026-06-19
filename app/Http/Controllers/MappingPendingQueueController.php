<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DprWorkItem;
use App\Models\ActivityMapping;
use App\Models\ActivityDivision;
use App\Helpers\AuditHelper;

class MappingPendingQueueController extends Controller
{
    public function index()
    {
        $pendingWorkItems = DprWorkItem::with([
            'dpr.project',
            'dpr.user',
            'activity',
            'activityMapping',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
        ])
        ->whereNull('activity_mapping_id')
        ->latest()
        ->paginate(10);

        return view('mapping-pending-queue.index', compact(
            'pendingWorkItems'
        ));
    }

    public function edit(DprWorkItem $dprWorkItem)
{
    $dprWorkItem->load([
        'dpr.project',
        'dpr.user',
        'activity',
        'block',
        'floor',
        'unit',
        'room',
        'subspace',
    ]);

    $activityDivisions = ActivityDivision::where('is_active', 1)
        ->orderBy('name')
        ->get();

    $activityMappings = ActivityMapping::where('is_active', 1)
        ->orderBy('activity_name')
        ->get();

    return view('mapping-pending-queue.edit', compact(
        'dprWorkItem',
        'activityDivisions',
        'activityMappings'
    ));
}

    public function update(Request $request, DprWorkItem $dprWorkItem)
    {
        $request->validate([
            'activity_mapping_id' => 'required|exists:activity_mappings,id',
        ]);

        $oldValues = [
    'activity_mapping_id' => $dprWorkItem->activity_mapping_id,
    'activity_id' => $dprWorkItem->activity_id,
    'quantity_completed' => $dprWorkItem->quantity_completed,
];

        $dprWorkItem->update([
    'activity_mapping_id' => $request->activity_mapping_id,
]);

$dprWorkItem->load([
    'dpr.project',
    'activity',
    'activityMapping',
]);

$newValues = [
    'activity_mapping_id' => $dprWorkItem->activity_mapping_id,
    'activity_id' => $dprWorkItem->activity_id,
    'quantity_completed' => $dprWorkItem->quantity_completed,
    'mapped_activity_name' => optional($dprWorkItem->activityMapping)->activity_name,
];

AuditHelper::log(
    'Mapping Pending Queue',
    'Mapped',
    'DprWorkItem',
    $dprWorkItem->id,
    'Activity mapping assigned for DPR work item',
    $oldValues,
    $newValues
);

        return redirect()
            ->route('mapping-pending-queue.index')
            ->with('success', 'Activity mapping updated successfully.');
    }
}