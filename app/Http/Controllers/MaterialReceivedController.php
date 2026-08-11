<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\BrandMaster;
use App\Models\Contractor;
use App\Models\MaterialGrade;
use App\Models\MaterialReceived;
use App\Models\MaterialReceivedPhoto;
use App\Models\MaterialSpecification;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\UnitMaster;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MaterialReceivedController extends Controller
{
    /**
     * Supported photo classifications.
     */
    private const PHOTO_TYPES = [
        'Material Photo',
        'Delivery Vehicle',
        'Unloading',
        'Challan',
        'Bill / Invoice',
        'Material Condition',
        'Storage Location',
        'Test Certificate',
        'Other',
    ];

    /**
     * Display Material Receipt headers.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $roleName = $user->role?->name;
        $isAccountant = $roleName === 'Accountant';

        $accountantStatusFilter = $request->input('accountant_status');

        $useDefaultAccountantQueue = $isAccountant
            && ! $request->has('status')
            && ! $request->has('accountant_status');

        $query = MaterialReceived::query()
            ->with([
                'project',
                'engineer',
                'block',
                'floor',
                'unit',
                'vendor',
                'contractor',
                'approver',
                'accountantVerifier',

                'items.activityDivision',
                'items.activity',
                'items.materialType.unit',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',

                'photos.materialReceivedItem.materialType',
                'photos.uploader',

                // Legacy relationships.
                'materialCategory',
                'material',
            ]);

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($useDefaultAccountantQueue) {
            $query
                ->where('status', 'Approved')
                ->where(function (Builder $builder) {
                    $builder
                        ->whereNull('accountant_verification_status')
                        ->orWhere(
                            'accountant_verification_status',
                            'Pending'
                        );
                });
        } else {
            if ($request->filled('status')) {
                $query->where(
                    'status',
                    $request->string('status')->toString()
                );
            }

            if (
                $request->filled('accountant_status')
                && $accountantStatusFilter !== 'all'
            ) {
                if ($accountantStatusFilter === 'Pending') {
                    $query->where(function (Builder $builder) {
                        $builder
                            ->whereNull('accountant_verification_status')
                            ->orWhere(
                                'accountant_verification_status',
                                'Pending'
                            );
                    });
                } else {
                    $query->where(
                        'accountant_verification_status',
                        $accountantStatusFilter
                    );
                }
            }
        }

        if ($request->filled('received_date')) {
            $query->whereDate(
                'received_date',
                $request->input('received_date')
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('challan_number', 'like', "%{$search}%")
                    ->orWhere('bill_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'project',
                        fn (Builder $projectQuery) =>
                            $projectQuery
                                ->where(
                                    'project_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_code',
                                    'like',
                                    "%{$search}%"
                                )
                    )
                    ->orWhereHas(
                        'items.materialType',
                        fn (Builder $typeQuery) =>
                            $typeQuery->where(
                                'material_type_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.brand',
                        fn (Builder $brandQuery) =>
                            $brandQuery->where(
                                'brand_name',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
        }

        $materialReceiveds = $query
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->paginate(7)
            ->withQueryString();

        $projects = $this->availableProjects();

        $todayReceipts = MaterialReceived::query()
            ->with('items')
            ->whereDate('received_date', today())
            ->get();

        $totalReceivedToday = $todayReceipts->sum(
            fn (MaterialReceived $receipt) =>
                $receipt->total_quantity_received
        );

        $draftCount = MaterialReceived::query()
            ->where('status', 'Draft')
            ->count();

        $submittedCount = MaterialReceived::query()
            ->where('status', 'Submitted')
            ->count();

        $approvedCount = MaterialReceived::query()
            ->where('status', 'Approved')
            ->count();

        $pendingAccountantCount = MaterialReceived::query()
            ->where('status', 'Approved')
            ->where(function (Builder $builder) {
                $builder
                    ->whereNull('accountant_verification_status')
                    ->orWhere(
                        'accountant_verification_status',
                        'Pending'
                    );
            })
            ->count();

        $billVerifiedTodayCount = MaterialReceived::query()
            ->where(
                'accountant_verification_status',
                'Bill Verified'
            )
            ->whereDate('accountant_verified_at', today())
            ->count();

        $effectiveStatusFilter = $useDefaultAccountantQueue
            ? 'Approved'
            : $request->input('status');

        $effectiveAccountantStatusFilter =
            $useDefaultAccountantQueue
                ? 'Pending'
                : ($accountantStatusFilter ?? 'all');

        return view(
            'material-received.index',
            compact(
                'materialReceiveds',
                'projects',
                'totalReceivedToday',
                'draftCount',
                'submittedCount',
                'approvedCount',
                'pendingAccountantCount',
                'billVerifiedTodayCount',
                'isAccountant',
                'effectiveStatusFilter',
                'effectiveAccountantStatusFilter'
            )
        );
    }

    /**
     * Show the multi-item receipt form.
     */
    public function create(): View
    {
        return view(
            'material-received.create',
            $this->formData()
        );
    }

    /**
     * Store a receipt header, material rows and uploaded photos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateReceipt($request);

        $this->validateItemRelationships(
            $validated['items']
        );

        $storedPaths = [];

        try {
            $materialReceived = DB::transaction(
                function () use (
                    $validated,
                    $request,
                    &$storedPaths
                ): MaterialReceived {
                    $vendor = ! empty($validated['vendor_id'])
                        ? Vendor::find($validated['vendor_id'])
                        : null;

                    $materialReceived = MaterialReceived::create([
                        'project_id' =>
                            (int) $validated['project_id'],

                        'user_id' => auth()->id(),

                        'project_block_id' =>
                            $validated['project_block_id'] ?? null,

                        'project_floor_id' =>
                            $validated['project_floor_id'] ?? null,

                        'project_unit_id' =>
                            $validated['project_unit_id'] ?? null,

                        'storage_location' =>
                            $this->nullableTrim(
                                $validated['storage_location'] ?? null
                            ),

                        'vendor_id' =>
                            $validated['vendor_id'] ?? null,

                        'vendor_name' =>
                            $vendor?->vendor_name,

                        'supplied_by_contractor' =>
                            (bool) (
                                $validated['supplied_by_contractor']
                                ?? false
                            ),

                        'contractor_id' =>
                            $validated['contractor_id'] ?? null,

                        'vehicle_number' =>
                            $this->nullableTrim(
                                $validated['vehicle_number'] ?? null
                            ),

                        'driver_name' =>
                            $this->nullableTrim(
                                $validated['driver_name'] ?? null
                            ),

                        'challan_number' =>
                            $this->nullableTrim(
                                $validated['challan_number'] ?? null
                            ),

                        'bill_number' =>
                            $this->nullableTrim(
                                $validated['bill_number'] ?? null
                            ),

                        'received_date' =>
                            $validated['received_date'],

                        'received_time' =>
                            now()->format('H:i:s'),

                        'material_condition' =>
                            'Pending Verification',

                        'site_engineer_verification_status' =>
                            'Pending',

                        'pmo_verification_status' =>
                            'Pending',

                        'accountant_verification_status' =>
                            'Pending',

                        'accepted_quantity' => 0,
                        'short_quantity' => 0,
                        'damaged_quantity' => 0,
                        'rejected_quantity' => 0,

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),

                        'status' => 'Draft',
                    ]);

                    $createdItems = [];

                    foreach (
                        array_values($validated['items'])
                        as $index => $item
                    ) {
                        $createdItems[$index] =
                            $materialReceived->items()->create([
                                'activity_division_id' =>
                                    $item['activity_division_id']
                                    ?? null,

                                'activity_id' =>
                                    $item['activity_id']
                                    ?? null,

                                'material_type_id' =>
                                    (int) $item['material_type_id'],

                                'brand_master_id' =>
                                    $item['brand_master_id']
                                    ?? null,

                                'material_specification_id' =>
                                    $item['material_specification_id']
                                    ?? null,

                                'material_grade_id' =>
                                    $item['material_grade_id']
                                    ?? null,

                                'quantity_received' =>
                                    $item['quantity_received'],

                                'unit_master_id' =>
                                    (int) $item['unit_master_id'],

                                'accepted_quantity' => 0,
                                'short_quantity' => 0,
                                'damaged_quantity' => 0,
                                'rejected_quantity' => 0,

                                'material_condition' =>
                                    'Pending Verification',

                                'sort_order' => $index + 1,

                                'remarks' =>
                                    $this->nullableTrim(
                                        $item['remarks'] ?? null
                                    ),
                            ]);
                    }

                    $this->storeUploadedPhotos(
                        request: $request,
                        materialReceived: $materialReceived,
                        createdItemsByIndex: $createdItems,
                        storedPaths: $storedPaths
                    );

                    $materialReceived->load(
                        $this->receiptRelationships()
                    );

                    AuditHelper::log(
                        'Material Received',
                        'Created',
                        'MaterialReceived',
                        $materialReceived->id,
                        'Material receipt created with '
                            . $materialReceived->items->count()
                            . ' material item(s) and '
                            . $materialReceived->photos->count()
                            . ' photo(s).',
                        null,
                        $this->auditValues($materialReceived)
                    );

                    return $materialReceived;
                }
            );

            return redirect()
                ->route(
                    'material-received.show',
                    $materialReceived
                )
                ->with(
                    'success',
                    'Material receipt created successfully as Draft.'
                );
        } catch (ValidationException $exception) {
            $this->deleteStoredPaths($storedPaths);

            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the material receipt.'
                );
        }
    }

    /**
     * Display a receipt.
     */
    public function show(
        MaterialReceived $materialReceived
    ): View {
        $materialReceived->load(
            $this->receiptRelationships()
        );

        return view(
            'material-received.show',
            compact('materialReceived')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
        MaterialReceived $materialReceived
    ): View {
        if ($materialReceived->status !== 'Draft') {
            abort(
                403,
                'Only Draft material receipts can be edited.'
            );
        }

        $materialReceived->load(
            $this->receiptRelationships()
        );

        return view(
            'material-received.edit',
            array_merge(
                compact('materialReceived'),
                $this->formData()
            )
        );
    }

    /**
     * Update the receipt header, replace item rows and manage photos.
     */
    public function update(
        Request $request,
        MaterialReceived $materialReceived
    ): RedirectResponse {
        if ($materialReceived->status !== 'Draft') {
            abort(
                403,
                'Only Draft material receipts can be updated.'
            );
        }

        $validated = $this->validateReceipt($request);

        $this->validateItemRelationships(
            $validated['items']
        );

        $storedPaths = [];
        $pathsToDeleteAfterCommit = [];

        try {
            DB::transaction(
                function () use (
                    $validated,
                    $request,
                    $materialReceived,
                    &$storedPaths,
                    &$pathsToDeleteAfterCommit
                ): void {
                    $materialReceived->load(
                        $this->receiptRelationships()
                    );

                    $oldValues = $this->auditValues(
                        $materialReceived
                    );

                    $vendor = ! empty($validated['vendor_id'])
                        ? Vendor::find($validated['vendor_id'])
                        : null;

                    $materialReceived->update([
                        'project_id' =>
                            (int) $validated['project_id'],

                        'project_block_id' =>
                            $validated['project_block_id'] ?? null,

                        'project_floor_id' =>
                            $validated['project_floor_id'] ?? null,

                        'project_unit_id' =>
                            $validated['project_unit_id'] ?? null,

                        'storage_location' =>
                            $this->nullableTrim(
                                $validated['storage_location'] ?? null
                            ),

                        'vendor_id' =>
                            $validated['vendor_id'] ?? null,

                        'vendor_name' =>
                            $vendor?->vendor_name,

                        'supplied_by_contractor' =>
                            (bool) (
                                $validated['supplied_by_contractor']
                                ?? false
                            ),

                        'contractor_id' =>
                            $validated['contractor_id'] ?? null,

                        'vehicle_number' =>
                            $this->nullableTrim(
                                $validated['vehicle_number'] ?? null
                            ),

                        'driver_name' =>
                            $this->nullableTrim(
                                $validated['driver_name'] ?? null
                            ),

                        'challan_number' =>
                            $this->nullableTrim(
                                $validated['challan_number'] ?? null
                            ),

                        'bill_number' =>
                            $this->nullableTrim(
                                $validated['bill_number'] ?? null
                            ),

                        'received_date' =>
                            $validated['received_date'],

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),
                    ]);

                    /*
                     * Capture existing photo-to-item associations before
                     * deleting and recreating the material rows.
                     */
                    $oldItemPositionById = $materialReceived->items
                        ->values()
                        ->mapWithKeys(
                            fn ($item, $index) => [
                                (int) $item->id => $index,
                            ]
                        );

                    $existingPhotos = $materialReceived->photos
                        ->map(function ($photo) use ($oldItemPositionById) {
                            return [
                                'id' => $photo->id,
                                'item_index' =>
                                    $photo->material_received_item_id
                                        ? $oldItemPositionById->get(
                                            (int) $photo->material_received_item_id
                                        )
                                        : null,
                            ];
                        })
                        ->keyBy('id');

                    $removePhotoIds = collect(
                        $validated['remove_photo_ids'] ?? []
                    )
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values();

                    if ($removePhotoIds->isNotEmpty()) {
                        $photosToRemove = $materialReceived->photos
                            ->whereIn('id', $removePhotoIds);

                        foreach ($photosToRemove as $photo) {
                            $pathsToDeleteAfterCommit[] =
                                $photo->file_path;

                            $photo->delete();
                        }
                    }

                    $materialReceived->items()->delete();

                    $createdItems = [];

                    foreach (
                        array_values($validated['items'])
                        as $index => $item
                    ) {
                        $createdItems[$index] =
                            $materialReceived->items()->create([
                                'activity_division_id' =>
                                    $item['activity_division_id']
                                    ?? null,

                                'activity_id' =>
                                    $item['activity_id']
                                    ?? null,

                                'material_type_id' =>
                                    (int) $item['material_type_id'],

                                'brand_master_id' =>
                                    $item['brand_master_id']
                                    ?? null,

                                'material_specification_id' =>
                                    $item['material_specification_id']
                                    ?? null,

                                'material_grade_id' =>
                                    $item['material_grade_id']
                                    ?? null,

                                'quantity_received' =>
                                    $item['quantity_received'],

                                'unit_master_id' =>
                                    (int) $item['unit_master_id'],

                                'accepted_quantity' => 0,
                                'short_quantity' => 0,
                                'damaged_quantity' => 0,
                                'rejected_quantity' => 0,

                                'material_condition' =>
                                    'Pending Verification',

                                'sort_order' => $index + 1,

                                'remarks' =>
                                    $this->nullableTrim(
                                        $item['remarks'] ?? null
                                    ),
                            ]);
                    }

                    /*
                     * Reconnect surviving old photos to the recreated item
                     * occupying the same row position.
                     */
                    $materialReceived->photos()
                        ->get()
                        ->each(function ($photo) use (
                            $existingPhotos,
                            $createdItems
                        ) {
                            $metadata = $existingPhotos->get(
                                $photo->id
                            );

                            if (! $metadata) {
                                return;
                            }

                            $itemIndex = $metadata['item_index'];

                            $photo->update([
                                'material_received_item_id' =>
                                    $itemIndex !== null
                                    && isset($createdItems[$itemIndex])
                                        ? $createdItems[$itemIndex]->id
                                        : null,
                            ]);
                        });

                    $this->storeUploadedPhotos(
                        request: $request,
                        materialReceived: $materialReceived,
                        createdItemsByIndex: $createdItems,
                        storedPaths: $storedPaths
                    );

                    $materialReceived->load(
                        $this->receiptRelationships()
                    );

                    AuditHelper::log(
                        'Material Received',
                        'Updated',
                        'MaterialReceived',
                        $materialReceived->id,
                        'Material receipt updated with '
                            . $materialReceived->photos->count()
                            . ' photo(s).',
                        $oldValues,
                        $this->auditValues($materialReceived)
                    );
                }
            );

            $this->deleteStoredPaths(
                $pathsToDeleteAfterCommit
            );

            return redirect()
                ->route(
                    'material-received.show',
                    $materialReceived
                )
                ->with(
                    'success',
                    'Material receipt updated successfully.'
                );
        } catch (ValidationException $exception) {
            $this->deleteStoredPaths($storedPaths);

            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the material receipt.'
                );
        }
    }

    /**
     * Submit a Draft receipt for approval.
     */
    public function submit(
        MaterialReceived $materialReceived
    ): RedirectResponse {
        if ($materialReceived->status !== 'Draft') {
            return back()->with(
                'error',
                'Only Draft material receipts can be submitted.'
            );
        }

        if (
            ! $materialReceived->items()->exists()
            && empty($materialReceived->material_id)
        ) {
            return back()->with(
                'error',
                'Add at least one material before submission.'
            );
        }

        $oldValues = [
            'status' =>
                $materialReceived->status,

            'site_engineer_verification_status' =>
                $materialReceived->site_engineer_verification_status,

            'submitted_at' =>
                $materialReceived->submitted_at,
        ];

        $materialReceived->update([
            'status' => 'Submitted',

            'site_engineer_verification_status' =>
                'Verified',

            'submitted_at' => now(),
        ]);

        $materialReceived->refresh();

        AuditHelper::log(
            'Material Received',
            'Submitted',
            'MaterialReceived',
            $materialReceived->id,
            'Material receipt submitted for approval.',
            $oldValues,
            [
                'status' =>
                    $materialReceived->status,

                'site_engineer_verification_status' =>
                    $materialReceived->site_engineer_verification_status,

                'submitted_at' =>
                    $materialReceived->submitted_at,
            ]
        );

        return back()->with(
            'success',
            'Material receipt submitted successfully.'
        );
    }

    /**
     * Approve a Submitted receipt.
     */
    public function approve(
        MaterialReceived $materialReceived
    ): RedirectResponse {
        if ($materialReceived->status !== 'Submitted') {
            return back()->with(
                'error',
                'Only Submitted material receipts can be approved.'
            );
        }

        $oldValues = [
            'status' =>
                $materialReceived->status,

            'pmo_verification_status' =>
                $materialReceived->pmo_verification_status,

            'approved_by' =>
                $materialReceived->approved_by,

            'approved_at' =>
                $materialReceived->approved_at,
        ];

        $materialReceived->update([
            'status' => 'Approved',

            'pmo_verification_status' =>
                'Approved',

            'approved_by' => auth()->id(),

            'approved_at' => now(),
        ]);

        $materialReceived->refresh();

        AuditHelper::log(
            'Material Received',
            'Approved',
            'MaterialReceived',
            $materialReceived->id,
            'Material receipt approved.',
            $oldValues,
            [
                'status' =>
                    $materialReceived->status,

                'pmo_verification_status' =>
                    $materialReceived->pmo_verification_status,

                'approved_by' =>
                    $materialReceived->approved_by,

                'approved_at' =>
                    $materialReceived->approved_at,
            ]
        );

        return back()->with(
            'success',
            'Material receipt approved successfully.'
        );
    }

    /**
     * Verify the supplier bill by Accounts.
     */
    public function accountantVerify(
        MaterialReceived $materialReceived
    ): RedirectResponse {
        if ($materialReceived->status !== 'Approved') {
            return back()->with(
                'error',
                'The material receipt must be approved by PMO before accountant verification.'
            );
        }

        if (
            $materialReceived->accountant_verification_status
            === 'Bill Verified'
        ) {
            return back()->with(
                'error',
                'This supplier bill has already been verified by Accounts.'
            );
        }

        $oldValues = [
            'accountant_verification_status' =>
                $materialReceived->accountant_verification_status,

            'accountant_verified_by' =>
                $materialReceived->accountant_verified_by,

            'accountant_verified_at' =>
                $materialReceived->accountant_verified_at,
        ];

        $materialReceived->update([
            'accountant_verification_status' =>
                'Bill Verified',

            'accountant_verified_by' =>
                auth()->id(),

            'accountant_verified_at' =>
                now(),
        ]);

        $materialReceived->refresh();

        AuditHelper::log(
            'Material Received',
            'Accountant Verified',
            'MaterialReceived',
            $materialReceived->id,
            'Supplier bill verified by Accounts.',
            $oldValues,
            [
                'accountant_verification_status' =>
                    $materialReceived->accountant_verification_status,

                'accountant_verified_by' =>
                    $materialReceived->accountant_verified_by,

                'accountant_verified_at' =>
                    $materialReceived->accountant_verified_at,
            ]
        );

        return back()->with(
            'success',
            'Supplier bill verified successfully by Accounts.'
        );
    }

    /**
     * Delete a single photo from a Draft receipt.
     *
     * A dedicated route can point here later if we want AJAX/image-card
     * deletion. Edit form removal also works through remove_photo_ids[].
     */
    public function destroyPhoto(
        MaterialReceived $materialReceived,
        MaterialReceivedPhoto $photo
    ): RedirectResponse {
        if ($materialReceived->status !== 'Draft') {
            abort(
                403,
                'Photos can only be removed while the receipt is Draft.'
            );
        }

        if (
            (int) $photo->material_received_id
            !== (int) $materialReceived->id
        ) {
            abort(404);
        }

        $oldValues = [
            'photo_id' => $photo->id,
            'photo_type' => $photo->photo_type,
            'file_path' => $photo->file_path,
            'caption' => $photo->caption,
        ];

        $filePath = $photo->file_path;
        $photoId = $photo->id;

        DB::transaction(function () use (
            $photo,
            $materialReceived,
            $oldValues,
            $photoId
        ): void {
            $photo->delete();

            AuditHelper::log(
                'Material Received',
                'Photo Deleted',
                'MaterialReceived',
                $materialReceived->id,
                'Material receipt photo deleted.',
                $oldValues,
                [
                    'photo_id' => $photoId,
                    'deleted' => true,
                ]
            );
        });

        $this->deleteStoredPaths([$filePath]);

        return back()->with(
            'success',
            'Photo removed successfully.'
        );
    }

    /**
     * Delete a Draft receipt only.
     */
    public function destroy(
        MaterialReceived $materialReceived
    ): RedirectResponse {
        if ($materialReceived->status !== 'Draft') {
            return back()->with(
                'error',
                'Only Draft material receipts can be deleted.'
            );
        }

        $materialReceived->load(
            $this->receiptRelationships()
        );

        $oldValues = $this->auditValues(
            $materialReceived
        );

        $photoPaths = $materialReceived->photos
            ->pluck('file_path')
            ->filter()
            ->values()
            ->all();

        DB::transaction(
            function () use (
                $materialReceived,
                $oldValues
            ): void {
                $receiptId = $materialReceived->id;

                $materialReceived->delete();

                AuditHelper::log(
                    'Material Received',
                    'Deleted',
                    'MaterialReceived',
                    $receiptId,
                    'Draft material receipt deleted.',
                    $oldValues,
                    null
                );
            }
        );

        $this->deleteStoredPaths($photoPaths);

        return redirect()
            ->route('material-received.index')
            ->with(
                'success',
                'Draft material receipt deleted successfully.'
            );
    }

    /**
     * Validate the receipt header, item rows and photo rows.
     */
    private function validateReceipt(Request $request): array
    {
        return $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'project_block_id' => [
                'nullable',
                'integer',
                'exists:project_blocks,id',
            ],

            'project_floor_id' => [
                'nullable',
                'integer',
                'exists:project_floors,id',
            ],

            'project_unit_id' => [
                'nullable',
                'integer',
                'exists:project_units,id',
            ],

            'storage_location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'vendor_id' => [
                'nullable',
                'integer',
                'exists:vendors,id',
            ],

            'supplied_by_contractor' => [
                'nullable',
                'boolean',
            ],

            'contractor_id' => [
                'nullable',
                'integer',
                'exists:contractors,id',
            ],

            'vehicle_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'driver_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'challan_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bill_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'received_date' => [
                'required',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.activity_division_id' => [
                'nullable',
                'integer',
                'exists:activity_divisions,id',
            ],

            'items.*.activity_id' => [
                'nullable',
                'integer',
                'exists:activities,id',
            ],

            'items.*.material_type_id' => [
                'required',
                'integer',
                'exists:material_types,id',
            ],

            'items.*.brand_master_id' => [
                'nullable',
                'integer',
                'exists:brand_masters,id',
            ],

            'items.*.material_specification_id' => [
                'nullable',
                'integer',
                'exists:material_specifications,id',
            ],

            'items.*.material_grade_id' => [
                'nullable',
                'integer',
                'exists:material_grades,id',
            ],

            'items.*.quantity_received' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_master_id' => [
                'required',
                'integer',
                'exists:unit_masters,id',
            ],

            'items.*.remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
             * Photo row format expected from Create/Edit Blade:
             *
             * photos[0][file]
             * photos[0][photo_type]
             * photos[0][caption]
             * photos[0][item_index]
             */
            'photos' => [
                'nullable',
                'array',
                'max:30',
            ],

            'photos.*.file' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'photos.*.photo_type' => [
                'nullable',
                'string',
                'in:' . implode(',', self::PHOTO_TYPES),
            ],

            'photos.*.caption' => [
                'nullable',
                'string',
                'max:500',
            ],

            'photos.*.item_index' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'remove_photo_ids' => [
                'nullable',
                'array',
            ],

            'remove_photo_ids.*' => [
                'integer',
                'exists:material_received_photos,id',
            ],
        ], [
            'items.required' =>
                'Add at least one material item.',

            'items.min' =>
                'Add at least one material item.',

            'items.*.material_type_id.required' =>
                'Select a Material Type for every row.',

            'items.*.quantity_received.gt' =>
                'Quantity received must be greater than zero.',

            'items.*.unit_master_id.required' =>
                'Every material row must have a unit.',

            'photos.max' =>
                'A maximum of 30 photos can be uploaded per receipt at one time.',

            'photos.*.file.image' =>
                'Every uploaded receipt file must be an image.',

            'photos.*.file.mimes' =>
                'Receipt photos must be JPG, JPEG, PNG or WEBP images.',

            'photos.*.file.max' =>
                'Each receipt photo may be up to 10 MB.',
        ]);
    }

    /**
     * Confirm that row selections belong to the selected Material Type.
     */
    private function validateItemRelationships(
        array $items
    ): void {
        $errors = [];

        foreach (array_values($items) as $index => $item) {
            $rowNumber = $index + 1;
            $materialTypeId = (int) $item['material_type_id'];

            $materialType = MaterialType::query()
                ->find($materialTypeId);

            if (! $materialType) {
                continue;
            }

            if (
                (int) $item['unit_master_id']
                !== (int) $materialType->unit_master_id
            ) {
                $errors["items.{$index}.unit_master_id"][] =
                    "Row {$rowNumber}: the unit does not match the selected Material Type.";
            }

            if (! empty($item['brand_master_id'])) {
                $brandValid = BrandMaster::query()
                    ->whereKey($item['brand_master_id'])
                    ->where(
                        'material_type_id',
                        $materialTypeId
                    )
                    ->where('is_active', true)
                    ->exists();

                if (! $brandValid) {
                    $errors[
                        "items.{$index}.brand_master_id"
                    ][] =
                        "Row {$rowNumber}: the selected Brand does not belong to the selected Material Type.";
                }
            }

            if (
                ! empty(
                    $item['material_specification_id']
                )
            ) {
                $specificationValid =
                    MaterialSpecification::query()
                        ->whereKey(
                            $item[
                                'material_specification_id'
                            ]
                        )
                        ->where(
                            'material_type_id',
                            $materialTypeId
                        )
                        ->where('is_active', true)
                        ->exists();

                if (! $specificationValid) {
                    $errors[
                        "items.{$index}.material_specification_id"
                    ][] =
                        "Row {$rowNumber}: the selected Specification does not belong to the selected Material Type.";
                }
            }

            if (! empty($item['material_grade_id'])) {
                $gradeValid = MaterialGrade::query()
                    ->whereKey($item['material_grade_id'])
                    ->where(
                        'material_type_id',
                        $materialTypeId
                    )
                    ->where('is_active', true)
                    ->exists();

                if (! $gradeValid) {
                    $errors[
                        "items.{$index}.material_grade_id"
                    ][] =
                        "Row {$rowNumber}: the selected Grade/Rating does not belong to the selected Material Type.";
                }
            }

            if (
                ! empty($item['activity_id'])
                && ! empty(
                    $item['activity_division_id']
                )
            ) {
                $activityValid = Activity::query()
                    ->whereKey($item['activity_id'])
                    ->where(
                        'activity_division_id',
                        $item['activity_division_id']
                    )
                    ->exists();

                if (! $activityValid) {
                    $errors[
                        "items.{$index}.activity_id"
                    ][] =
                        "Row {$rowNumber}: the selected Activity does not belong to the selected Activity Division.";
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    /**
     * Data required by create and edit forms.
     */
    private function formData(): array
    {
        $materialTypes = MaterialType::query()
            ->with('unit')
            ->where('is_active', true)
            ->orderBy('material_group')
            ->orderBy('sequence')
            ->orderBy('material_type_name')
            ->get();

        return [
            'projects' => $this->availableProjects(),

            'projectBlocks' => ProjectBlock::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'projectFloors' => ProjectFloor::query()
                ->where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),

            'projectUnits' => ProjectUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'contractors' => Contractor::query()
                ->where('status', 1)
                ->orderBy('contractor_name')
                ->get(),

            'vendors' => Vendor::query()
                ->where('is_active', true)
                ->orderBy('vendor_name')
                ->get(),

            'activityDivisions' =>
                ActivityDivision::query()
                    ->where('is_active', true)
                    ->orderBy('sequence')
                    ->orderBy('name')
                    ->get(),

            'activities' => Activity::query()
                ->where('is_active', true)
                ->orderBy('activity_division_id')
                ->orderBy('activity_name')
                ->get(),

            'materialTypes' => $materialTypes,

            'materialGroups' => $materialTypes
                ->pluck('material_group')
                ->filter()
                ->unique()
                ->sort()
                ->values(),

            'brands' => BrandMaster::query()
                ->where('is_active', true)
                ->whereNotNull('material_type_id')
                ->orderBy('material_type_id')
                ->orderBy('sequence')
                ->orderBy('brand_name')
                ->get(),

            'specifications' =>
                MaterialSpecification::query()
                    ->where('is_active', true)
                    ->whereNotNull('material_type_id')
                    ->orderBy('material_type_id')
                    ->orderBy('sequence')
                    ->orderBy('specification_name')
                    ->get(),

            'grades' => MaterialGrade::query()
                ->where('is_active', true)
                ->orderBy('material_type_id')
                ->orderBy('sequence')
                ->orderBy('grade_name')
                ->get(),

            'units' => UnitMaster::query()
                ->where('is_active', true)
                ->orderBy('unit_name')
                ->get(),

            'photoTypes' => self::PHOTO_TYPES,
        ];
    }

    /**
     * Projects available to the current user.
     */
    private function availableProjects()
    {
        $user = auth()->user();

        if (
            in_array(
                $user->role?->name,
                ['Admin', 'PMO', 'DGM'],
                true
            )
        ) {
            return Project::query()
                ->where('status', 'Active')
                ->orderBy('project_name')
                ->get();
        }

        return $user->projects()
            ->where('status', 'Active')
            ->orderBy('project_name')
            ->get();
    }

    /**
     * Receipt relationships.
     */
    private function receiptRelationships(): array
    {
        return [
            'project',
            'engineer',
            'block',
            'floor',
            'unit',
            'vendor',
            'contractor',
            'approver',
            'accountantVerifier',

            'items.activityDivision',
            'items.activity',
            'items.materialType.unit',
            'items.brand',
            'items.specification',
            'items.grade',
            'items.unit',
            'items.photos.uploader',

            'photos.materialReceivedItem.materialType',
            'photos.uploader',

            // Legacy support.
            'materialCategory',
            'material',
        ];
    }

    /**
     * Store newly uploaded receipt photos.
     */
    private function storeUploadedPhotos(
        Request $request,
        MaterialReceived $materialReceived,
        array $createdItemsByIndex,
        array &$storedPaths
    ): void {
        $photoRows = $request->input('photos', []);
        $uploadedFiles = $request->file('photos', []);

        if (! is_array($uploadedFiles)) {
            return;
        }

        $project = $materialReceived->project
            ?? Project::find($materialReceived->project_id);

        $engineer = $materialReceived->engineer
            ?? auth()->user();

        $datePart = $materialReceived->received_date
            ? $materialReceived->received_date->format('Ymd')
            : now()->format('Ymd');

        $timePart = now()->format('His');

        $existingPhotoCount = $materialReceived
            ->photos()
            ->count();

        $sequence = $existingPhotoCount + 1;

        foreach ($uploadedFiles as $rowIndex => $fileData) {
            $uploadedFile = is_array($fileData)
                ? ($fileData['file'] ?? null)
                : null;

            if (! $uploadedFile) {
                continue;
            }

            $metadata = $photoRows[$rowIndex] ?? [];

            $photoType = $this->normalizePhotoType(
                $metadata['photo_type']
                    ?? 'Material Photo'
            );

            $caption = $this->nullableTrim(
                $metadata['caption'] ?? null
            );

            $itemIndex = isset($metadata['item_index'])
                && $metadata['item_index'] !== ''
                    ? (int) $metadata['item_index']
                    : null;

            $materialItem = $itemIndex !== null
                && isset($createdItemsByIndex[$itemIndex])
                    ? $createdItemsByIndex[$itemIndex]
                    : null;

            if ($materialItem) {
                $materialItem->loadMissing('materialType');
            }

            $materialName = $materialItem
                ?->materialType
                ?->material_type_name
                ?? 'General';

            $extension = strtolower(
                $uploadedFile->getClientOriginalExtension()
                ?: $uploadedFile->extension()
                ?: 'jpg'
            );

            $filename = $this->buildPhotoFilename(
                projectName: $project?->project_name ?? 'Project',
                materialName: $materialName,
                photoType: $photoType,
                engineerName: $engineer?->name ?? 'Engineer',
                datePart: $datePart,
                timePart: $timePart,
                sequence: $sequence,
                extension: $extension
            );

            $directory = implode('/', [
                'material-received',
                'project-' . $materialReceived->project_id,
                'receipt-' . $materialReceived->id,
            ]);

            $path = $uploadedFile->storeAs(
                $directory,
                $filename,
                'public'
            );

            $storedPaths[] = $path;

            $materialReceived->photos()->create([
                'material_received_item_id' =>
                    $materialItem?->id,

                'uploaded_by' =>
                    auth()->id(),

                'photo_type' =>
                    $photoType,

                'file_path' =>
                    $path,

                'original_name' =>
                    $uploadedFile->getClientOriginalName(),

                'mime_type' =>
                    $uploadedFile->getMimeType(),

                'file_size' =>
                    $uploadedFile->getSize(),

                'caption' =>
                    $caption,

                'sort_order' =>
                    $sequence,
            ]);

            $sequence++;
        }
    }

    /**
     * Human-readable but safe and unique file naming convention:
     *
     * ProjectName-MaterialName-PhotoType-EngineerName-
     * YYYYMMDD-HHMMSS-Sequence.ext
     */
    private function buildPhotoFilename(
        string $projectName,
        string $materialName,
        string $photoType,
        string $engineerName,
        string $datePart,
        string $timePart,
        int $sequence,
        string $extension
    ): string {
        $parts = [
            $this->filenamePart($projectName, 50),
            $this->filenamePart($materialName, 50),
            $this->filenamePart($photoType, 35),
            $this->filenamePart($engineerName, 40),
            $datePart,
            $timePart,
            str_pad(
                (string) $sequence,
                3,
                '0',
                STR_PAD_LEFT
            ),
        ];

        return implode('-', $parts)
            . '.'
            . strtolower($extension);
    }

    /**
     * Convert any human-readable field to a filesystem-safe filename part.
     */
    private function filenamePart(
        string $value,
        int $maxLength
    ): string {
        $slug = Str::slug(
            Str::limit(
                trim($value),
                $maxLength,
                ''
            ),
            '-'
        );

        return $slug !== ''
            ? $slug
            : 'NA';
    }

    private function normalizePhotoType(
        mixed $photoType
    ): string {
        $photoType = trim((string) $photoType);

        return in_array(
            $photoType,
            self::PHOTO_TYPES,
            true
        )
            ? $photoType
            : 'Other';
    }

    /**
     * Remove files after a failed transaction or after DB deletion commits.
     */
    private function deleteStoredPaths(
        array $paths
    ): void {
        $paths = collect($paths)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete(
                $paths
            );
        }
    }

    /**
     * Values stored in the audit trail.
     */
    private function auditValues(
        MaterialReceived $materialReceived
    ): array {
        $materialReceived->loadMissing(
            $this->receiptRelationships()
        );

        return [
            'id' =>
                $materialReceived->id,

            'dpr_id' =>
                $materialReceived->dpr_id,

            'project_id' =>
                $materialReceived->project_id,

            'user_id' =>
                $materialReceived->user_id,

            'project_block_id' =>
                $materialReceived->project_block_id,

            'project_floor_id' =>
                $materialReceived->project_floor_id,

            'project_unit_id' =>
                $materialReceived->project_unit_id,

            'storage_location' =>
                $materialReceived->storage_location,

            'vendor_id' =>
                $materialReceived->vendor_id,

            'vendor_name' =>
                $materialReceived->vendor_name,

            'contractor_id' =>
                $materialReceived->contractor_id,

            'vehicle_number' =>
                $materialReceived->vehicle_number,

            'driver_name' =>
                $materialReceived->driver_name,

            'challan_number' =>
                $materialReceived->challan_number,

            'bill_number' =>
                $materialReceived->bill_number,

            'received_date' =>
                $materialReceived->received_date?->format(
                    'Y-m-d'
                ),

            'status' =>
                $materialReceived->status,

            'site_engineer_verification_status' =>
                $materialReceived->site_engineer_verification_status,

            'pmo_verification_status' =>
                $materialReceived->pmo_verification_status,

            'accountant_verification_status' =>
                $materialReceived->accountant_verification_status,

            'submitted_at' =>
                $materialReceived->submitted_at,

            'approved_at' =>
                $materialReceived->approved_at,

            'approved_by' =>
                $materialReceived->approved_by,

            'accountant_verified_at' =>
                $materialReceived->accountant_verified_at,

            'accountant_verified_by' =>
                $materialReceived->accountant_verified_by,

            'remarks' =>
                $materialReceived->remarks,

            'items' => $materialReceived->items
                ->map(
                    fn ($item) => [
                        'id' =>
                            $item->id,

                        'activity_division_id' =>
                            $item->activity_division_id,

                        'activity_id' =>
                            $item->activity_id,

                        'material_type_id' =>
                            $item->material_type_id,

                        'material_type_name' =>
                            $item->materialType
                                ?->material_type_name,

                        'brand_master_id' =>
                            $item->brand_master_id,

                        'brand_name' =>
                            $item->brand?->brand_name,

                        'material_specification_id' =>
                            $item->material_specification_id,

                        'specification_name' =>
                            $item->specification
                                ?->specification_name,

                        'material_grade_id' =>
                            $item->material_grade_id,

                        'grade_name' =>
                            $item->grade?->grade_name,

                        'quantity_received' =>
                            $item->quantity_received,

                        'unit_master_id' =>
                            $item->unit_master_id,

                        'unit_name' =>
                            $item->unit?->unit_name,

                        'sort_order' =>
                            $item->sort_order,

                        'remarks' =>
                            $item->remarks,
                    ]
                )
                ->values()
                ->all(),

            'photos' => $materialReceived->photos
                ->map(
                    fn ($photo) => [
                        'id' =>
                            $photo->id,

                        'material_received_item_id' =>
                            $photo->material_received_item_id,

                        'material_name' =>
                            $photo->material_name,

                        'photo_type' =>
                            $photo->photo_type,

                        'file_path' =>
                            $photo->file_path,

                        'original_name' =>
                            $photo->original_name,

                        'mime_type' =>
                            $photo->mime_type,

                        'file_size' =>
                            $photo->file_size,

                        'caption' =>
                            $photo->caption,

                        'sort_order' =>
                            $photo->sort_order,

                        'uploaded_by' =>
                            $photo->uploaded_by,
                    ]
                )
                ->values()
                ->all(),
        ];
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === ''
            ? null
            : $trimmed;
    }
}
