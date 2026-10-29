<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RfqController extends Controller
{
    private function authorizeRfq(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && $user->isActive() && $user->hasPermission($permission),
            403,
            'You do not have permission to perform this RFQ action.'
        );
    }

    public function index()
{
    $this->authorizeRfq('rfqs.view');

    $rfqs = Rfq::query()
        ->with('project')
        ->withCount(['items', 'invitations'])
        ->orderByDesc('id')
        ->paginate(20);

    return view('rfqs.index', compact('rfqs'));
}

    public function create()
    {
        $this->authorizeRfq('rfqs.create');
        $projects = DB::table('projects')->select('id', 'project_name')->orderBy('project_name')->get();
        $vendors = DB::table('vendors')->where('is_active', 1)->select('id', 'vendor_name', 'material_category_id')->orderBy('vendor_name')->get();
        return view('rfqs.create', compact('projects', 'vendors'));
    }

    public function requirementItems(Request $request)
    {
        $this->authorizeRfq('rfqs.create');
        $data = $request->validate(['project_id' => 'required|integer|exists:projects,id']);
        $items = DB::table('material_requirement_items as mri')
            ->join('material_requirements as mr', 'mr.id', '=', 'mri.material_requirement_id')
            ->join('material_types as mt', 'mt.id', '=', 'mri.material_type_id')
            ->leftJoin('material_variants as mv', 'mv.id', '=', 'mri.material_variant_id')
            ->leftJoin('unit_masters as um', 'um.id', '=', 'mri.unit_master_id')
            ->where('mr.project_id', $data['project_id'])
            ->where('mr.status', 'Approved')
            ->where('mt.is_active', 1)
            ->where('mt.is_legacy', 0)
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')->from('material_bible_product_mappings as mb')
                    ->whereColumn('mb.material_type_id', 'mri.material_type_id')
                    ->where('mb.is_active', 1);
            })
            ->whereRaw('(mri.required_quantity - COALESCE(mri.fulfilled_quantity, 0)) > 0')
            ->select('mri.id', 'mri.material_type_id', 'mri.material_variant_id', 'mri.brand_master_id',
                'mri.material_specification_id', 'mri.material_grade_id', 'mri.unit_master_id',
                'mri.specification_text', 'mri.required_quantity', 'mri.fulfilled_quantity',
                'mt.material_type_name as product_name', 'mv.variant_name', 'um.unit_code', 'mr.id as requirement_id')
            ->orderBy('mr.id')->orderBy('mri.sort_order')->limit(500)->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $this->authorizeRfq('rfqs.create');
        $data = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'submission_deadline' => 'required|date|after:now',
            'delivery_address' => 'nullable|string|max:2000',
            'delivery_contact_name' => 'nullable|string|max:150',
            'delivery_contact_phone' => 'nullable|string|max:30',
            'vendor_instructions' => 'nullable|string|max:5000',
            'internal_remarks' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1|max:100',
            'items.*.requirement_item_id' => 'required|integer|distinct|exists:material_requirement_items,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.vendor_ids' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                if (count($value) !== count(array_unique(array_map('strval', $value)))) {
                    $fail('The same vendor cannot be selected twice for one material.');
                }
            }],
            'items.*.vendor_ids.*' => 'required|integer|exists:vendors,id',
        ]);

        $rfq = DB::transaction(function () use ($data) {
            $rfq = Rfq::create([
                'rfq_number' => 'RFQ-PENDING-' . Str::upper(Str::random(12)),
                'project_id' => $data['project_id'], 'rfq_date' => now()->toDateString(),
                'submission_deadline' => $data['submission_deadline'], 'status' => 'Draft',
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_contact_name' => $data['delivery_contact_name'] ?? null,
                'delivery_contact_phone' => $data['delivery_contact_phone'] ?? null,
                'vendor_instructions' => $data['vendor_instructions'] ?? null,
                'internal_remarks' => $data['internal_remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);
            $rfq->update(['rfq_number' => 'RFQ-' . now()->format('Y') . '-' . str_pad((string) $rfq->id, 5, '0', STR_PAD_LEFT)]);
            $vendorItems = [];
            foreach ($data['items'] as $position => $input) {
                $source = DB::table('material_requirement_items as mri')
                    ->join('material_requirements as mr', 'mr.id', '=', 'mri.material_requirement_id')
                    ->join('material_types as mt', 'mt.id', '=', 'mri.material_type_id')
                    ->where('mri.id', $input['requirement_item_id'])
                    ->where('mr.project_id', $data['project_id'])->where('mr.status', 'Approved')
                    ->where('mt.is_active', 1)->where('mt.is_legacy', 0)
                    ->select('mri.*', 'mt.material_type_name')->first();
                if (!$source) throw ValidationException::withMessages(['items' => 'One or more requirement items are not eligible.']);
                $eligible = DB::table('material_bible_product_mappings')
                    ->where('material_type_id', $source->material_type_id)->where('is_active', 1)->exists();
                if (!$eligible) throw ValidationException::withMessages(['items' => 'A product is not in the active Material Bible.']);
                $remaining = (float)$source->required_quantity - (float)$source->fulfilled_quantity;
                if ($remaining <= 0 || (float)$input['quantity'] > $remaining + 0.000001)
                    throw ValidationException::withMessages(['items' => 'Requested quantity exceeds outstanding requirement quantity.']);
                $item = $rfq->items()->create([
                    'material_requirement_item_id' => $source->id, 'material_type_id' => $source->material_type_id,
                    'material_variant_id' => $source->material_variant_id, 'brand_master_id' => $source->brand_master_id,
                    'material_specification_id' => $source->material_specification_id,
                    'material_grade_id' => $source->material_grade_id, 'unit_master_id' => $source->unit_master_id,
                    'product_name' => $source->material_type_name, 'specification_text' => $source->specification_text,
                    'requested_quantity' => $input['quantity'], 'sort_order' => $position + 1,
                ]);
                foreach ($input['vendor_ids'] as $vendorId) {
                    if (!DB::table('vendors')->where('id', $vendorId)->where('is_active', 1)->exists())
                        throw ValidationException::withMessages(['items' => 'Inactive vendor selected.']);
                    $vendorItems[$vendorId][] = $item->id;
                }
            }
            foreach ($vendorItems as $vendorId => $itemIds) {
                $invitation = $rfq->invitations()->create([
                    'vendor_id' => $vendorId, 'status' => 'Draft',
                    // No usable public token until the secure invitation dispatch step.
                    'token_hash' => hash('sha256', Str::random(64)),
                    'expires_at' => $data['submission_deadline'],
                ]);
                foreach ($itemIds as $itemId) {
                    DB::table('rfq_invitation_items')->insert([
                        'rfq_vendor_invitation_id' => $invitation->id, 'rfq_item_id' => $itemId,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
            return $rfq;
        });
        return redirect()->route('rfqs.show', $rfq)->with('success', 'RFQ draft created. Vendor links have not been issued.');
    }

    public function show(Rfq $rfq)
    {
        $this->authorizeRfq('rfqs.view');
        $rfq->load(['project', 'items', 'invitations.vendor', 'invitations.items']);
        return view('rfqs.show', compact('rfq'));
    }
}
