<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RfqComparisonController extends Controller
{
    public function show(Request $request, int $rfq)
    {
        $user = $request->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('rfqs.view'), 403);

        $header = DB::table('rfqs as r')
            ->leftJoin('projects as p', 'p.id', '=', 'r.project_id')
            ->where('r.id', $rfq)
            ->select('r.*', 'p.project_name')
            ->first();
        abort_unless($header, 404);

        $items = DB::table('rfq_items')
            ->where('rfq_id', $rfq)
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        // Only final submitted quotations are eligible for procurement comparison.
        // Use latest submitted revision for each invitation, if multiple exist.
        $submitted = DB::table('rfq_quotations as q')
            ->join('rfq_vendor_invitations as i', 'i.id', '=', 'q.rfq_vendor_invitation_id')
            ->join('vendors as v', 'v.id', '=', 'i.vendor_id')
            ->where('i.rfq_id', $rfq)
            ->where('q.status', 'Submitted')
            ->where('i.status', 'Submitted')
            ->whereNull('i.revoked_at')
            ->select('q.*', 'i.vendor_id', 'v.vendor_name')
            ->orderByDesc('q.revision_number')->orderByDesc('q.id')
            ->get()
            ->unique('rfq_vendor_invitation_id')
            ->values();

        $quoteIds = $submitted->pluck('id')->all();
        $quoteLines = count($quoteIds)
            ? DB::table('rfq_quotation_items')
                ->whereIn('rfq_quotation_id', $quoteIds)
                ->get()->groupBy('rfq_item_id')
            : collect();

        $assigned = DB::table('rfq_invitation_items as a')
            ->join('rfq_vendor_invitations as i', 'i.id', '=', 'a.rfq_vendor_invitation_id')
            ->where('i.rfq_id', $rfq)
            ->select('a.rfq_item_id', 'a.rfq_vendor_invitation_id')
            ->get()->groupBy('rfq_item_id');

        $comparison = $items->map(function ($item) use ($submitted, $quoteLines, $assigned) {
            $allowedInvitationIds = ($assigned->get($item->id) ?? collect())
                ->pluck('rfq_vendor_invitation_id')->all();
            $offers = $submitted->filter(fn ($q) => in_array($q->rfq_vendor_invitation_id, $allowedInvitationIds))
                ->map(function ($q) use ($item, $quoteLines) {
                    $line = ($quoteLines->get($item->id) ?? collect())
                        ->firstWhere('rfq_quotation_id', $q->id);
                    if (!$line) return null;
                    $qty = (float) $item->requested_quantity;
                    $rate = (float) $line->rate;
                    $discount = (float) $line->discount_amount;
                    $base = (float) $line->product_amount;
                    $tax = (float) $line->tax_amount;
                    return (object) [
                        'vendor_name' => $q->vendor_name,
                        'vendor_id' => $q->vendor_id,
                        'quotation_id' => $q->id,
                        'quotation_item_id' => $line->id,
                        'rate' => $rate,
                        'discount_total' => $discount,
                        'discount_percent' => $qty > 0 && $rate > 0 ? round($discount / ($qty * $rate) * 100, 2) : 0,
                        'tax_percent' => (float) $line->tax_percent,
                        'taxable' => $base,
                        'gst' => $tax,
                        'total' => (float) $line->line_total,
                    ];
                })->filter()->values();
            $min = $offers->min('total');
            return (object) ['item' => $item, 'offers' => $offers, 'lowest' => $min];
        });

        return view('rfqs.comparison', compact('header', 'comparison', 'submitted'));
    }
}
