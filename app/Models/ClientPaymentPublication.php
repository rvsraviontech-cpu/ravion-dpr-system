<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPaymentPublication extends Model
{
    protected $table = 'client_payment_publications';

    protected $fillable = [
        'contract_id',
        'revision',
        'client_safe_snapshot',
        'status',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'revision' => 'integer',
            'client_safe_snapshot' => 'array',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientPaymentContract::class, 'contract_id');
    }
}
