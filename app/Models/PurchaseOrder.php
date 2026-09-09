<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_ISSUED = 'Issued';
    public const STATUS_PARTIALLY_RECEIVED = 'Partially Received';
    public const STATUS_RECEIVED = 'Received';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'po_number',
        'project_id',
        'vendor_id',
        'po_date',
        'expected_delivery_date',
        'vendor_reference',
        'vendor_name',
        'vendor_gst_number',
        'vendor_address',
        'vendor_contact_person',
        'vendor_mobile',
        'vendor_email',
        'project_name',
        'delivery_address',
        'payment_terms',
        'delivery_terms',
        'freight_terms',
        'currency',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'tax_amount',
        'other_charges',
        'grand_total',
        'status',
        'internal_remarks',
        'vendor_notes',
        'terms_conditions',
        'created_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'issued_by',
        'issued_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_APPROVED,
            self::STATUS_ISSUED,
            self::STATUS_PARTIALLY_RECEIVED,
            self::STATUS_RECEIVED,
            self::STATUS_CLOSED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED,
        ], true);
    }
}
