<?php

namespace App\Http\Controllers;

use App\Models\MaterialDispatch;
use App\Models\MaterialDispatchItem;
use App\Models\MaterialDispatchReceipt;
use App\Models\MaterialDispatchReceiptItem;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MaterialDispatchReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaterialDispatch::query()
            ->with(['project', 'items', 'receipts'])
            ->whereIn('status', [
                MaterialDispatch::STATUS_DISPATCHED,
                MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
            ])
            ->orderBy('expected_delivery_date')
            ->orderBy('dispatch_date')
            ->orderBy('id');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('dispatch_number', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('challan_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $dispatches = $query->paginate(25)->withQueryString();

        $projects = Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_code', 'project_name']);

        return view('incoming-materials.index', compact('dispatches', 'projects'));
    }

    public function create(MaterialDispatch $materialDispatch): View|RedirectResponse
    {
        if (!in_array($materialDispatch->status, [
            MaterialDispatch::STATUS_DISPATCHED,
            MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
        ], true)) {
            return redirect()
                ->route('incoming-materials.index')
                ->with('error', 'This dispatch is not pending site receipt.');
        }

        $materialDispatch->load(['project', 'items.allocations']);

        $receiptRows = $materialDispatch->items
            ->map(function (MaterialDispatchItem $item) {
                return [
                    'material_dispatch_item_id' => $item->id,
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'specification_text' => $item->specification_text,
                    'brand_name' => $item->brand_name,
                    'unit_name' => $item->unit_name,
                    'unit_code' => $item->unit_code,
                    'dispatched_quantity' => (float) $item->dispatched_quantity,
                    'previously_received_quantity' => (float) $item->received_quantity,
                    'previously_short_quantity' => (float) $item->short_quantity,
                    'remaining_quantity' => $this->remainingAccountableQuantity($item),
                ];
            })
            ->filter(fn (array $row) => $row['remaining_quantity'] > 0)
            ->values();

        if ($receiptRows->isEmpty()) {
            $this->refreshDispatchStatus($materialDispatch);

            return redirect()
                ->route('incoming-materials.index')
                ->with('success', 'This dispatch has already been fully accounted for.');
        }

        return view('incoming-materials.receive', compact('materialDispatch', 'receiptRows'));
    }

    public function store(Request $request, MaterialDispatch $materialDispatch): RedirectResponse
    {
        if (!in_array($materialDispatch->status, [
            MaterialDispatch::STATUS_DISPATCHED,
            MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
        ], true)) {
            return back()->with('error', 'This dispatch is no longer available for receipt.');
        }

        $validated = $this->validateReceipt($request);

        $receipt = DB::transaction(function () use ($validated, $materialDispatch) {
            $lockedDispatch = MaterialDispatch::query()
                ->whereKey($materialDispatch->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($lockedDispatch->status, [
                MaterialDispatch::STATUS_DISPATCHED,
                MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
            ], true)) {
                throw ValidationException::withMessages([
                    'receipt' => 'This dispatch is no longer available for receipt.',
                ]);
            }

            $dispatchItems = MaterialDispatchItem::query()
                ->where('material_dispatch_id', $lockedDispatch->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $preparedRows = $this->prepareAndValidateRows(
                $validated['items'],
                $dispatchItems
            );

            $receipt = MaterialDispatchReceipt::create([
                'material_dispatch_id' => $lockedDispatch->id,
                'receipt_number' => $this->nextReceiptNumber(),
                'project_id' => $lockedDispatch->project_id,
                'receipt_date' => $validated['receipt_date'],
                'dispatch_number' => $lockedDispatch->dispatch_number,
                'project_name' => $lockedDispatch->project_name,
                'dispatch_from' => $lockedDispatch->dispatch_from,
                'challan_number' => $validated['challan_number'] ?? $lockedDispatch->challan_number,
                'vehicle_number' => $validated['vehicle_number'] ?? $lockedDispatch->vehicle_number,
                'driver_name' => $validated['driver_name'] ?? $lockedDispatch->driver_name,
                'status' => MaterialDispatchReceipt::STATUS_DRAFT,
                'receipt_remarks' => $validated['receipt_remarks'] ?? null,
                'internal_remarks' => $validated['internal_remarks'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($preparedRows as $index => $row) {
                $dispatchItem = $row['dispatch_item'];

                MaterialDispatchReceiptItem::create([
                    'material_dispatch_receipt_id' => $receipt->id,
                    'material_dispatch_item_id' => $dispatchItem->id,
                    'material_type_id' => $dispatchItem->material_type_id,
                    'brand_master_id' => $dispatchItem->brand_master_id,
                    'unit_master_id' => $dispatchItem->unit_master_id,
                    'product_name' => $dispatchItem->product_name,
                    'product_code' => $dispatchItem->product_code,
                    'specification_text' => $dispatchItem->specification_text,
                    'brand_name' => $dispatchItem->brand_name,
                    'unit_name' => $dispatchItem->unit_name,
                    'unit_code' => $dispatchItem->unit_code,
                    'dispatched_quantity' => $dispatchItem->dispatched_quantity,
                    'previously_received_quantity' => $dispatchItem->received_quantity,
                    'received_quantity' => $row['received_quantity'],
                    'accepted_quantity' => $row['accepted_quantity'],
                    'short_quantity' => $row['short_quantity'],
                    'damaged_quantity' => $row['damaged_quantity'],
                    'rejected_quantity' => $row['rejected_quantity'],
                    'remarks' => $row['remarks'],
                    'sort_order' => $index + 1,
                ]);
            }

            return $receipt;
        });

        return redirect()
            ->route('dispatch-receipts.show', $receipt)
            ->with('success', 'Receipt draft saved. Review it and confirm the receipt.');
    }

    public function show(MaterialDispatchReceipt $materialDispatchReceipt): View
    {
        $materialDispatchReceipt->load([
            'materialDispatch.project',
            'project',
            'createdBy',
            'receivedBy',
            'items.materialDispatchItem',
            'items.materialType',
            'items.brand',
            'items.unit',
        ]);

        return view('incoming-materials.show', compact('materialDispatchReceipt'));
    }

    public function confirm(MaterialDispatchReceipt $materialDispatchReceipt): RedirectResponse
    {
        if (!$materialDispatchReceipt->isDraft()) {
            return back()->with('error', 'Only Draft receipts can be confirmed.');
        }

        DB::transaction(function () use ($materialDispatchReceipt) {
            $receipt = MaterialDispatchReceipt::query()
                ->whereKey($materialDispatchReceipt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($receipt->status !== MaterialDispatchReceipt::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'receipt' => 'This receipt has already been processed.',
                ]);
            }

            $dispatch = MaterialDispatch::query()
                ->whereKey($receipt->material_dispatch_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($dispatch->status, [
                MaterialDispatch::STATUS_DISPATCHED,
                MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
            ], true)) {
                throw ValidationException::withMessages([
                    'receipt' => 'The source dispatch is no longer open for receipt.',
                ]);
            }

            $receipt->load('items');

            if ($receipt->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'receipt' => 'The receipt does not contain any material items.',
                ]);
            }

            foreach ($receipt->items as $receiptItem) {
                $dispatchItem = MaterialDispatchItem::query()
                    ->whereKey($receiptItem->material_dispatch_item_id)
                    ->where('material_dispatch_id', $dispatch->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->validateReceiptItemBalance($receiptItem);

                $remaining = $this->remainingAccountableQuantity($dispatchItem);
                $accountedNow = round(
                    (float) $receiptItem->received_quantity
                    + (float) $receiptItem->short_quantity,
                    3
                );

                if ($accountedNow <= 0) {
                    throw ValidationException::withMessages([
                        'receipt' => "Receipt item {$receiptItem->product_name} has no quantity to post.",
                    ]);
                }

                if ($accountedNow > round($remaining, 3)) {
                    throw ValidationException::withMessages([
                        'receipt' => "{$receiptItem->product_name}: this receipt accounts for "
                            . number_format($accountedNow, 3)
                            . ', but only '
                            . number_format($remaining, 3)
                            . ' remains on the dispatch.',
                    ]);
                }

                $dispatchItem->update([
                    'received_quantity' => (float) $dispatchItem->received_quantity + (float) $receiptItem->received_quantity,
                    'accepted_quantity' => (float) $dispatchItem->accepted_quantity + (float) $receiptItem->accepted_quantity,
                    'short_quantity' => (float) $dispatchItem->short_quantity + (float) $receiptItem->short_quantity,
                    'damaged_quantity' => (float) $dispatchItem->damaged_quantity + (float) $receiptItem->damaged_quantity,
                    'rejected_quantity' => (float) $dispatchItem->rejected_quantity + (float) $receiptItem->rejected_quantity,
                ]);
            }

            $receipt->update([
                'status' => MaterialDispatchReceipt::STATUS_RECEIVED,
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);

            $this->refreshDispatchStatus($dispatch);
        });

        return redirect()
            ->route('dispatch-receipts.show', $materialDispatchReceipt)
            ->with('success', 'Receipt confirmed successfully. Dispatch quantities have been updated.');
    }

    private function validateReceipt(Request $request): array
    {
        return $request->validate([
            'receipt_date' => ['required', 'date'],
            'challan_number' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:150'],
            'receipt_remarks' => ['nullable', 'string', 'max:3000'],
            'internal_remarks' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.material_dispatch_item_id' => ['required', 'integer', 'exists:material_dispatch_items,id'],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.accepted_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.short_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.damaged_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.rejected_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function prepareAndValidateRows(array $rows, $dispatchItems): array
    {
        $prepared = [];
        $errors = [];

        foreach (array_values($rows) as $index => $row) {
            $rowNumber = $index + 1;
            $dispatchItemId = (int) $row['material_dispatch_item_id'];
            $dispatchItem = $dispatchItems->get($dispatchItemId);

            if (!$dispatchItem) {
                $errors["items.{$index}.material_dispatch_item_id"][] =
                    "Row {$rowNumber}: this item does not belong to the selected dispatch.";
                continue;
            }

            $received = round((float) $row['received_quantity'], 3);
            $accepted = round((float) $row['accepted_quantity'], 3);
            $short = round((float) $row['short_quantity'], 3);
            $damaged = round((float) $row['damaged_quantity'], 3);
            $rejected = round((float) $row['rejected_quantity'], 3);

            $physicalAccounted = round($accepted + $damaged + $rejected, 3);

            if ($received !== $physicalAccounted) {
                $errors["items.{$index}.received_quantity"][] =
                    "Row {$rowNumber} ({$dispatchItem->product_name}): Receive Now must equal Accepted + Damaged + Rejected.";
            }

            $accountedNow = round($received + $short, 3);
            $remaining = round($this->remainingAccountableQuantity($dispatchItem), 3);

            if ($accountedNow > $remaining) {
                $errors["items.{$index}.received_quantity"][] =
                    "Row {$rowNumber} ({$dispatchItem->product_name}): Received + Short cannot exceed the remaining "
                    . number_format($remaining, 3)
                    . ' '
                    . ($dispatchItem->unit_name ?: '');
            }

            if ($accountedNow <= 0) {
                continue;
            }

            $prepared[] = [
                'dispatch_item' => $dispatchItem,
                'received_quantity' => $received,
                'accepted_quantity' => $accepted,
                'short_quantity' => $short,
                'damaged_quantity' => $damaged,
                'rejected_quantity' => $rejected,
                'remarks' => $row['remarks'] ?? null,
            ];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        if (empty($prepared)) {
            throw ValidationException::withMessages([
                'items' => 'Enter a received or short quantity for at least one material item.',
            ]);
        }

        return $prepared;
    }

    private function validateReceiptItemBalance(MaterialDispatchReceiptItem $item): void
    {
        $received = round((float) $item->received_quantity, 3);
        $physicalAccounted = round(
            (float) $item->accepted_quantity
            + (float) $item->damaged_quantity
            + (float) $item->rejected_quantity,
            3
        );

        if ($received !== $physicalAccounted) {
            throw ValidationException::withMessages([
                'receipt' => "{$item->product_name}: Receive Now must equal Accepted + Damaged + Rejected.",
            ]);
        }
    }

    private function remainingAccountableQuantity(MaterialDispatchItem $item): float
    {
        return max(
            0,
            round(
                (float) $item->dispatched_quantity
                - (float) $item->received_quantity
                - (float) $item->short_quantity,
                3
            )
        );
    }

    private function refreshDispatchStatus(MaterialDispatch $materialDispatch): void
    {
        $materialDispatch->refresh()->load('items');

        $hasAnyAccounting = false;
        $allAccounted = true;

        foreach ($materialDispatch->items as $item) {
            $accounted = round(
                (float) $item->received_quantity + (float) $item->short_quantity,
                3
            );
            $dispatched = round((float) $item->dispatched_quantity, 3);

            if ($accounted > 0) {
                $hasAnyAccounting = true;
            }

            if ($accounted < $dispatched) {
                $allAccounted = false;
            }
        }

        if ($allAccounted && $materialDispatch->items->isNotEmpty()) {
            $materialDispatch->update([
                'status' => MaterialDispatch::STATUS_RECEIVED,
            ]);
            return;
        }

        if ($hasAnyAccounting) {
            $materialDispatch->update([
                'status' => MaterialDispatch::STATUS_PARTIALLY_RECEIVED,
            ]);
            return;
        }

        $materialDispatch->update([
            'status' => MaterialDispatch::STATUS_DISPATCHED,
        ]);
    }

    private function nextReceiptNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "MDR-{$year}-";

        $last = MaterialDispatchReceipt::query()
            ->where('receipt_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('receipt_number');

        $next = 1;

        if ($last) {
            $parts = explode('-', $last);
            $next = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
