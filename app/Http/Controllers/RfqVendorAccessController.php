<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RfqVendorAccessController extends Controller
{
    /** Issues one usable link. This endpoint MUST be behind auth middleware. */
    public function issue(Request $request, int $invitation)
    {
        $user = $request->user();
        abort_unless($user && $user->isActive() && $user->hasPermission('rfqs.invitations.manage'), 403);

        $result = DB::transaction(function () use ($invitation) {
            $record = DB::table('rfq_vendor_invitations as i')
                ->join('rfqs as r', 'r.id', '=', 'i.rfq_id')
                ->where('i.id', $invitation)
                ->select('i.*', 'r.status as rfq_status', 'r.submission_deadline')
                ->lockForUpdate()->first();

            abort_unless($record, 404);
            // Expected business-rule failures return to the RFQ page, not Laravel's exception screen.
            $back = fn (string $message) => redirect()->route('rfqs.show', ['rfq' => $record->rfq_id])
                ->with('rfq_invitation_error', $message);

            if ($record->status === 'Submitted') {
                return ['error_response' => $back('This vendor has already submitted their quotation. A new invitation link cannot be generated.')];
            }
            if ($record->status === 'Revoked' || $record->revoked_at) {
                return ['error_response' => $back('This vendor invitation has been revoked. A new link cannot be generated.')];
            }
            if (in_array($record->rfq_status, ['Cancelled', 'Closed'], true)) {
                return ['error_response' => $back('This RFQ is closed or cancelled. Vendor links cannot be generated.')];
            }
            if (!$record->submission_deadline || now()->greaterThanOrEqualTo($record->submission_deadline)) {
                return ['error_response' => $back('The RFQ submission deadline has passed. Please extend it before generating a vendor link.')];
            }
            if (!DB::table('rfq_invitation_items')->where('rfq_vendor_invitation_id', $invitation)->exists()) {
                return ['error_response' => $back('No materials are assigned to this vendor invitation.')];
            }

            $token = bin2hex(random_bytes(32));
            $expiry = now()->addDays(7);
            if ($expiry->greaterThan($record->submission_deadline)) $expiry = \Carbon\Carbon::parse($record->submission_deadline);

            DB::table('rfq_vendor_invitations')->where('id', $invitation)->update([
                'token_hash' => hash('sha256', $token),
                'status' => 'Issued',
                'sent_at' => now(),
                'expires_at' => $expiry,
                'revoked_at' => null,
                'updated_at' => now(),
            ]);

            return ['token' => $token, 'expires_at' => $expiry->toDateTimeString()];
        });

        if (isset($result['error_response'])) {
            return $result['error_response'];
        }

        // The plaintext token is returned once and never saved to the database.
        // Do not log this response or expose it in analytics.
        return response()->view('vendor-rfqs.issued', [
            'url' => route('vendor-rfqs.show', ['token' => $result['token']]),
            'expiresAt' => $result['expires_at'],
        ])->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }

    /** Public vendor quotation form. */
    public function show(string $token)
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/D', $token), 404);
        $hash = hash('sha256', $token);
        $invitation = DB::table('rfq_vendor_invitations as i')
            ->join('rfqs as r', 'r.id', '=', 'i.rfq_id')
            ->join('vendors as v', 'v.id', '=', 'i.vendor_id')
            ->leftJoin('projects as p', 'p.id', '=', 'r.project_id')
            ->where('i.token_hash', $hash)
            ->select('i.*', 'r.rfq_number', 'r.status as rfq_status', 'r.submission_deadline',
                'r.vendor_instructions', 'p.project_name', 'v.vendor_name')
            ->first();

        abort_unless($invitation, 404);
        abort_if($invitation->revoked_at || in_array($invitation->status, ['Draft', 'Revoked'], true), 403, 'Invitation unavailable.');
        abort_if(in_array($invitation->rfq_status, ['Cancelled', 'Closed'], true), 403, 'RFQ unavailable.');
        abort_if(!$invitation->expires_at || now()->greaterThanOrEqualTo($invitation->expires_at)
            || now()->greaterThanOrEqualTo($invitation->submission_deadline), 403, 'Invitation expired.');

        $items = DB::table('rfq_invitation_items as ii')
            ->join('rfq_items as ri', 'ri.id', '=', 'ii.rfq_item_id')
            ->leftJoin('unit_masters as um', 'um.id', '=', 'ri.unit_master_id')
            ->where('ii.rfq_vendor_invitation_id', $invitation->id)
            ->orderBy('ri.sort_order')
            ->select('ri.id', 'ri.product_name', 'ri.specification_text', 'ri.requested_quantity', 'um.unit_code')
            ->get();

        DB::table('rfq_vendor_invitations')->where('id', $invitation->id)->whereNull('viewed_at')
            ->update(['viewed_at' => now(), 'updated_at' => now()]);

        $quotation = DB::table('rfq_quotations')
            ->where('rfq_vendor_invitation_id', $invitation->id)
            ->orderByDesc('revision_number')->orderByDesc('id')->first();
        $savedItems = $quotation
            ? DB::table('rfq_quotation_items')->where('rfq_quotation_id', $quotation->id)
                ->get()->keyBy('rfq_item_id')
            : collect();
        $locked = $invitation->status === 'Submitted' || ($quotation && $quotation->status === 'Submitted');

        return response()->view('vendor-rfqs.show', compact('invitation', 'items', 'quotation', 'savedItems', 'locked'))
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    /** Public POST endpoint. The token is the only credential; never trust posted item IDs. */
    public function save(Request $request, string $token)
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/D', $token), 404);
        $action = $request->input('action');
        abort_unless(in_array($action, ['draft', 'submit'], true), 422, 'Invalid action.');

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:300'],
            'gst_enabled' => ['required', 'boolean'],
            'items.*.discount_mode' => ['nullable', 'in:percent,amount'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'between:0,100'],
            'items.*.discount_per_unit' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'items.*.rate' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'items.*.tax_percent' => ['required', 'numeric', 'between:0,100'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
            'payment_terms' => ['nullable', 'string', 'max:2000'],
            'delivery_terms' => ['nullable', 'string', 'max:2000'],
            'vendor_remarks' => ['nullable', 'string', 'max:4000'],
            'valid_until' => ['nullable', 'date'],
            'auto_charges' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'hamali_charges' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'loading_charges' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'unloading_charges' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
        ]);

        DB::transaction(function () use ($token, $action, $data) {
            $invitation = DB::table('rfq_vendor_invitations as i')
                ->join('rfqs as r', 'r.id', '=', 'i.rfq_id')
                ->where('i.token_hash', hash('sha256', $token))
                ->select('i.*', 'r.status as rfq_status', 'r.submission_deadline')
                ->lockForUpdate()->first();

            abort_unless($invitation, 404);
            abort_if($invitation->revoked_at || in_array($invitation->status, ['Draft', 'Revoked', 'Submitted'], true), 403);
            abort_if(in_array($invitation->rfq_status, ['Cancelled', 'Closed'], true), 403);
            abort_if(!$invitation->expires_at || now()->greaterThanOrEqualTo($invitation->expires_at)
                || now()->greaterThanOrEqualTo($invitation->submission_deadline), 403, 'Invitation expired.');

            $assigned = DB::table('rfq_invitation_items')
                ->where('rfq_vendor_invitation_id', $invitation->id)
                ->pluck('rfq_item_id')->map(fn ($id) => (string) $id)->sort()->values()->all();
            $posted = array_map('strval', array_keys($data['items']));
            sort($posted);
            abort_unless($assigned === $posted && count($assigned) > 0, 422, 'Submitted materials do not match assigned materials.');

            $quote = DB::table('rfq_quotations')
                ->where('rfq_vendor_invitation_id', $invitation->id)
                ->orderByDesc('revision_number')->orderByDesc('id')
                ->lockForUpdate()->first();
            abort_if($quote && $quote->status === 'Submitted', 403, 'Quotation already submitted.');

            $requestedQuantities = DB::table('rfq_items')->whereIn('id', array_map('intval', $assigned))->pluck('requested_quantity', 'id');
            $gstEnabled = (bool) $data['gst_enabled'];
            $productTotal = 0.0;
            $taxTotal = 0.0;
            $lines = [];
            foreach ($data['items'] as $itemId => $line) {
                $qty = round((float) $requestedQuantities->get((int) $itemId), 3);
                abort_if($qty <= 0, 422, 'Requested quantity must be positive.');
                $rate = round((float) $line['rate'], 2);
                $mode = $line['discount_mode'] ?? 'amount';
                $percent = round((float) ($line['discount_percent'] ?? 0), 4);
                $discountPerUnit = $mode === 'percent'
                    ? round($rate * $percent / 100, 2)
                    : round((float) ($line['discount_per_unit'] ?? 0), 2);
                if ($discountPerUnit > $rate) {
                    throw ValidationException::withMessages(["items.$itemId.discount_per_unit" => 'Discount per unit cannot exceed rate per unit.']);
                }
                $discount = round($qty * $discountPerUnit, 2);
                $gst = $gstEnabled ? round((float) $line['tax_percent'], 2) : 0.0;
                $gross = round($qty * $rate, 2);
                if ($discount > $gross) {
                    throw ValidationException::withMessages(["items.$itemId.discount_per_unit" => 'Total discount cannot exceed gross line amount.']);
                }
                $base = round($gross - $discount, 2);
                $tax = round($base * $gst / 100, 2);
                $productTotal += $base;
                $taxTotal += $tax;
                $lines[] = [
                    'rfq_item_id' => (int) $itemId,
                    'offered_quantity' => $qty,
                    'rate' => $rate,
                    'discount_amount' => $discount,
                    'tax_percent' => $gst,
                    'product_amount' => $base,
                    'tax_amount' => $tax,
                    'line_total' => round($base + $tax, 2),
                    'remarks' => $line['remarks'] ?? null,
                ];
            }
            $charges = [];
            foreach (['auto_charges', 'hamali_charges', 'loading_charges', 'unloading_charges'] as $chargeKey) {
                $charges[$chargeKey] = round((float) ($data[$chargeKey] ?? 0), 2);
            }
            $other = round(array_sum($charges), 2);
            $values = [
                'status' => $action === 'submit' ? 'Submitted' : 'Draft',
                'gst_enabled' => $gstEnabled,
                'valid_until' => $data['valid_until'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'delivery_terms' => $data['delivery_terms'] ?? null,
                'vendor_remarks' => $data['vendor_remarks'] ?? null,
                'product_amount' => round($productTotal, 2),
                'tax_amount' => round($taxTotal, 2),
                'other_charges' => $other,
                ...$charges,
                'net_total_amount' => round($productTotal + $taxTotal + $other, 2),
                'submitted_at' => $action === 'submit' ? now() : null,
                'updated_at' => now(),
            ];
            if ($quote) {
                DB::table('rfq_quotations')->where('id', $quote->id)->update($values);
                $quoteId = $quote->id;
                DB::table('rfq_quotation_items')->where('rfq_quotation_id', $quoteId)->delete();
            } else {
                $quoteId = DB::table('rfq_quotations')->insertGetId(array_merge($values, [
                    'rfq_vendor_invitation_id' => $invitation->id,
                    'revision_number' => 1,
                    'created_at' => now(),
                ]));
            }
            foreach ($lines as $line) {
                DB::table('rfq_quotation_items')->insert(array_merge($line, [
                    'rfq_quotation_id' => $quoteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            if ($action === 'submit') {
                DB::table('rfq_vendor_invitations')->where('id', $invitation->id)->update([
                    'status' => 'Submitted', 'submitted_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('vendor-rfqs.show', ['token' => $token])
            ->with('success', $action === 'submit' ? 'Quotation submitted successfully. It is now locked.' : 'Draft quotation saved.');
    }

}
