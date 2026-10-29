<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RfqAwardController extends Controller
{
    private function authorizeProcurement(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('rfqs.view')
            && $user->hasPermission('purchase_orders.create'), 403);
    }

    public function review(Request $request, int $rfq): View
    {
        $this->authorizeProcurement($request);
        $selection = $this->validateSelection($request);
        $prepared = $this->prepare($rfq, $selection);
        return view('rfqs.award-review', $prepared + ['rfqId' => $rfq, 'selection' => $selection]);
    }

    public function store(Request $request, int $rfq): RedirectResponse
    {
        $this->authorizeProcurement($request);
        $selection = $this->validateSelection($request);
        $created = DB::transaction(function () use ($rfq, $selection, $request) {
            // Serialize awards for this RFQ; allocations are rechecked inside the transaction.
            $header = DB::table('rfqs')->where('id', $rfq)->lockForUpdate()->first();
            abort_unless($header, 404);
            $prepared = $this->prepare($rfq, $selection);
            $poIds = [];
            foreach ($prepared['groups'] as $vendorId => $group) {
                $vendor = DB::table('vendors')->where('id', $vendorId)->first();
                if (!$vendor || !$vendor->is_active) {
                    throw ValidationException::withMessages(['awards' => 'A selected vendor is inactive.']);
                }
                $po = PurchaseOrder::create([
                    'po_number' => $this->nextPoNumber(),
                    'project_id' => $header->project_id,
                    'vendor_id' => $vendorId,
                    'po_date' => now()->toDateString(),
                    'vendor_name' => $vendor->vendor_name,
                    'vendor_gst_number' => $vendor->gst_number,
                    'vendor_address' => implode(', ', array_filter([$vendor->address, $vendor->city, $vendor->state, $vendor->pincode])),
                    'vendor_contact_person' => $vendor->contact_person,
                    'vendor_mobile' => $vendor->mobile,
                    'vendor_email' => $vendor->email,
                    'project_name' => $prepared['projectName'],
                    'delivery_address' => $header->delivery_address,
                    'delivery_contact_name' => $header->delivery_contact_name,
                    'delivery_contact_phone' => $header->delivery_contact_phone,
                    'currency' => 'INR',
                    'status' => PurchaseOrder::STATUS_DRAFT,
                    'internal_remarks' => 'Created from '.$header->rfq_number.'; quotation charges excluded pending procurement review.',
                    'other_charges' => 0,
                    'created_by' => $request->user()->id,
                ]);
                $totals = ['subtotal' => 0, 'discount_amount' => 0, 'taxable_amount' => 0, 'tax_amount' => 0, 'grand_total' => 0];
                foreach ($group['lines'] as $index => $line) {
                    $item = $line['item'];
                    $qty = $line['qty'];
                    $gross = $line['gross'];
                    $discount = $line['discount'];
                    $taxable = $line['taxable'];
                    $tax = $line['tax'];
                    $amount = $line['total'];
                    $poItem = $po->items()->create([
                        'material_type_id' => $item->material_type_id,
                        'material_variant_id' => $item->material_variant_id,
                        'material_specification_id' => $item->material_specification_id,
                        'material_grade_id' => $item->material_grade_id,
                        'brand_master_id' => $item->brand_master_id,
                        'unit_master_id' => $item->unit_master_id,
                        'product_name' => $item->product_name,
                        'specification_text' => $item->specification_text,
                        'ordered_quantity' => $qty,
                        'received_quantity' => 0,
                        'rate' => $line['rate'],
                        'discount_percent' => $gross > 0 ? round($discount / $gross * 100, 4) : 0,
                        'discount_amount' => $discount,
                        'taxable_amount' => $taxable,
                        'tax_percent' => $line['gst'],
                        'tax_amount' => $tax,
                        'line_amount' => $amount,
                        'sort_order' => $index + 1,
                        'remarks' => 'RFQ '.$header->rfq_number.' quotation line #'.$line['quotationItemId'],
                    ]);
                    $poItem->allocations()->create([
                        'material_requirement_item_id' => $item->material_requirement_item_id,
                        'allocated_quantity' => $qty,
                        'received_quantity' => 0,
                    ]);
                    DB::table('rfq_award_items')->insert([
                        'rfq_id' => $rfq,
                        'rfq_item_id' => $item->id,
                        'rfq_quotation_item_id' => $line['quotationItemId'],
                        'purchase_order_id' => $po->id,
                        'purchase_order_item_id' => $poItem->id,
                        'awarded_quantity' => $qty,
                        'created_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    foreach (['subtotal' => $gross, 'discount_amount' => $discount, 'taxable_amount' => $taxable, 'tax_amount' => $tax, 'grand_total' => $amount] as $key => $value) {
                        $totals[$key] += $value;
                    }
                }
                $po->update(array_map(fn ($value) => round($value, 2), $totals));
                $poIds[] = $po->id;
            }
            return $poIds;
        }, 3);

        return redirect()->route('rfqs.comparison', $rfq)
            ->with('success', count($created).' Draft Purchase Order(s) created: #'.implode(', #', $created));
    }

    private function validateSelection(Request $request): array
    {
        $validated = Validator::make($request->all(), [
            'awards' => ['required', 'array', 'min:1'],
            'awards.*.quotation_item_id' => ['required', 'integer', 'exists:rfq_quotation_items,id'],
            'awards.*.quantity' => ['required', 'numeric', 'gt:0'],
        ])->validate();
        return $validated['awards'];
    }

    private function prepare(int $rfq, array $selection): array
    {
        $header = DB::table('rfqs as r')->join('projects as p', 'p.id', '=', 'r.project_id')
            ->where('r.id', $rfq)->select('r.*', 'p.project_name')->first();
        abort_unless($header, 404);
        $groups = [];
        $seen = [];
        foreach ($selection as $rfqItemId => $selected) {
            $item = DB::table('rfq_items')->where('id', (int)$rfqItemId)->where('rfq_id', $rfq)->first();
            if (!$item || !$item->material_requirement_item_id || isset($seen[$item->id])) {
                throw ValidationException::withMessages(['awards' => 'Invalid or duplicate RFQ material selection.']);
            }
            $seen[$item->id] = true;
            $quote = DB::table('rfq_quotation_items as qi')
                ->join('rfq_quotations as q', 'q.id', '=', 'qi.rfq_quotation_id')
                ->join('rfq_vendor_invitations as inv', 'inv.id', '=', 'q.rfq_vendor_invitation_id')
                ->join('rfq_invitation_items as assigned', function ($join) {
                    $join->on('assigned.rfq_vendor_invitation_id', '=', 'inv.id')
                        ->on('assigned.rfq_item_id', '=', 'qi.rfq_item_id');
                })
                ->where('qi.id', $selected['quotation_item_id'])
                ->where('qi.rfq_item_id', $item->id)
                ->where('inv.rfq_id', $rfq)
                ->where('q.status', 'Submitted')->where('inv.status', 'Submitted')
                ->whereNull('inv.revoked_at')
                ->select('qi.*', 'q.rfq_vendor_invitation_id', 'inv.vendor_id', 'q.id as quotation_id')
                ->first();
            if (!$quote) {
                throw ValidationException::withMessages(['awards' => 'Only assigned, final submitted quotation lines can be awarded.']);
            }
            $latestId = DB::table('rfq_quotations')->where('rfq_vendor_invitation_id', $quote->rfq_vendor_invitation_id)
                ->where('status', 'Submitted')->orderByDesc('revision_number')->orderByDesc('id')->value('id');
            if ((int)$latestId !== (int)$quote->quotation_id) {
                throw ValidationException::withMessages(['awards' => 'A newer quotation revision is available. Refresh comparison.']);
            }
            $mr = DB::table('material_requirement_items as m')
                ->join('material_requirements as r', 'r.id', '=', 'm.material_requirement_id')
                ->where('m.id', $item->material_requirement_item_id)
                ->select('m.required_quantity', 'r.project_id', 'r.status')->first();
            if (!$mr || $mr->status !== 'Approved' || (int)$mr->project_id !== (int)$header->project_id) {
                throw ValidationException::withMessages(['awards' => 'Material Requirement is not approved or belongs to another project.']);
            }
            $ordered = (float) DB::table('purchase_order_item_allocations as a')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'a.purchase_order_item_id')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->where('a.material_requirement_item_id', $item->material_requirement_item_id)
                ->whereNotIn('po.status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
                ->sum('a.allocated_quantity');
            $awarded = (float) DB::table('rfq_award_items')->where('rfq_item_id', $item->id)->sum('awarded_quantity');
            $remaining = max(0, round(min(
                (float) $item->requested_quantity - $awarded,
                (float) $mr->required_quantity - $ordered
            ), 3));
            $qty = round((float) $selected['quantity'], 3);
            if ($remaining < 0.001) {
                throw ValidationException::withMessages(['awards' => $item->product_name.' is already fully ordered or awarded. Refresh the comparison and select only materials with remaining quantity.']);
            }
            if ($qty > $remaining + 0.0005) {
                throw ValidationException::withMessages(['awards' => $item->product_name.' has only '.$remaining.' remaining to award/order.']);
            }
            $rate = round((float)$quote->rate, 4);
            $quoteQty = (float)$quote->offered_quantity;
            if ($quoteQty <= 0 || $qty > $quoteQty + 0.0005) {
                throw ValidationException::withMessages(['awards' => 'Award quantity exceeds quoted quantity for '.$item->product_name]);
            }
            $gross = round($qty * $rate, 2);
            $discount = round(((float)$quote->discount_amount / $quoteQty) * $qty, 2);
            $taxable = round($gross - $discount, 2);
            if ($taxable < 0) {
                throw ValidationException::withMessages(['awards' => 'Discount exceeds gross amount.']);
            }
            $gst = (float)$quote->tax_percent;
            $tax = round($taxable * $gst / 100, 2);
            $line = compact('item', 'qty', 'gross', 'discount', 'taxable', 'tax', 'rate', 'gst') + [
                'total' => round($taxable + $tax, 2), 'quotationItemId' => $quote->id,
            ];
            $vendorId = (int)$quote->vendor_id;
            if (!isset($groups[$vendorId])) {
                $vendor = DB::table('vendors')->find($vendorId);
                $groups[$vendorId] = ['vendorName' => $vendor?->vendor_name ?? 'Vendor', 'lines' => []];
            }
            $groups[$vendorId]['lines'][] = $line;
        }
        return ['groups' => $groups, 'projectName' => $header->project_name, 'rfqNumber' => $header->rfq_number];
    }

    private function nextPoNumber(): string
    {
        // Serialize numbering through the same PO table lock as normal creation.
        $prefix = 'PO-'.now()->format('Y').'-';
        $last = PurchaseOrder::query()->where('po_number', 'like', $prefix.'%')
            ->lockForUpdate()->orderByDesc('po_number')->value('po_number');
        $next = $last && preg_match('/(\d+)$/', $last, $m) ? (int)$m[1] + 1 : 1;
        return $prefix.str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}
