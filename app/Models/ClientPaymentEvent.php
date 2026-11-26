<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPaymentEvent extends Model
{
    protected $table = 'client_payment_events';

    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'milestone_id',
        'receipt_id',
        'actor_guard',
        'actor_id',
        'action',
        'before_values',
        'after_values',
        'reason',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientPaymentContract::class, 'contract_id');
    }
}
