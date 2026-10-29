<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use Illuminate\Support\Facades\DB;

class RfqPrintController extends Controller
{
    public function show(Rfq $rfq, int $invitation)
    {
        $user = auth()->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('rfqs.view'), 403);

        $assignment = DB::table('rfq_vendor_invitations')
            ->where('id', $invitation)
            ->where('rfq_id', $rfq->id)
            ->first();
        abort_unless($assignment, 404);

        $vendor = DB::table('vendors')->where('id', $assignment->vendor_id)->first();
        abort_unless($vendor, 404);

        $items = DB::table('rfq_invitation_items as ai')
            ->join('rfq_items as i', 'i.id', '=', 'ai.rfq_item_id')
            ->leftJoin('unit_masters as u', 'u.id', '=', 'i.unit_master_id')
            ->where('ai.rfq_vendor_invitation_id', $assignment->id)
            ->where('i.rfq_id', $rfq->id)
            ->select('i.id', 'i.product_name', 'i.specification_text',
                'i.requested_quantity', 'u.unit_code')
            ->orderBy('i.sort_order')
            ->get();

        abort_if($items->isEmpty(), 404);

        $project = DB::table('projects')->where('id', $rfq->project_id)->first();

        return response()
            ->view('rfqs.print', compact('rfq', 'assignment', 'vendor', 'items', 'project'))
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
