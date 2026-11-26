<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPaymentContract extends Model
{
    protected $table = 'client_payment_contracts';

    protected $fillable = [
        'project_id',
        'contract_reference',
        'agreement_date',
        'currency',
        'agreed_amount',
        'approved_variations',
        'payment_terms',
        'status',
        'revision',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'agreement_date' => 'date',
            'agreed_amount' => 'decimal:2',
            'approved_variations' => 'decimal:2',
            'revision' => 'integer',
        ];
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ClientPaymentMilestone::class, 'contract_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ClientPaymentReceipt::class, 'contract_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ClientPaymentEvent::class, 'contract_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(ClientPaymentPublication::class, 'contract_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
