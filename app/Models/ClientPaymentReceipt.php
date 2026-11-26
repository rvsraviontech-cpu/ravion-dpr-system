<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPaymentReceipt extends Model
{
    protected $table = 'client_payment_receipts';

    protected $fillable = [
        'contract_id',
        'milestone_id',
        'receipt_reference',
        'received_on',
        'amount',
        'payment_method',
        'transaction_reference',
        'remarks',
        'status',
        'recorded_by',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'received_on' => 'date',
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientPaymentContract::class, 'contract_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ClientPaymentMilestone::class, 'milestone_id');
    }
}
