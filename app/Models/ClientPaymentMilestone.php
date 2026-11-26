<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPaymentMilestone extends Model
{
    protected $table = 'client_payment_milestones';

    protected $fillable = [
        'contract_id',
        'milestone_code',
        'title',
        'description',
        'type',
        'work_stage_id',
        'scheduled_amount',
        'scheduled_percent',
        'planned_due_date',
        'certified_on',
        'due_date',
        'status',
        'sort_order',
        'revision',
        'approved_at',
        'approved_by',
        'published_at',
        'published_by',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_amount' => 'decimal:2',
            'scheduled_percent' => 'decimal:4',
            'planned_due_date' => 'date',
            'certified_on' => 'date',
            'due_date' => 'date',
            'sort_order' => 'integer',
            'revision' => 'integer',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientPaymentContract::class, 'contract_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ClientPaymentReceipt::class, 'milestone_id');
    }

    public function workStage(): BelongsTo
    {
        return $this->belongsTo(ClientWorkStage::class, 'work_stage_id');
    }
}
