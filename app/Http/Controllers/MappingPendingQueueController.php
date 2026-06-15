<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DprWorkItem;
use App\Models\ActivityMapping;
use App\Models\ActivityDivision;

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

        $dprWorkItem->update([
            'activity_mapping_id' => $request->activity_mapping_id,
        ]);

        return redirect()
            ->route('mapping-pending-queue.index')
            ->with('success', 'Activity mapping updated successfully.');
    }
}