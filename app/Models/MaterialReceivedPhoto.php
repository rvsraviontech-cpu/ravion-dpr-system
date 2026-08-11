<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialReceivedPhoto extends Model
{
    protected $fillable = [
        'material_received_id',
        'material_received_item_id',

        'uploaded_by',

        'photo_type',

        'file_path',
        'original_name',
        'mime_type',
        'file_size',

        'caption',

        'sort_order',
    ];

    protected $casts = [
        'material_received_id' => 'integer',
        'material_received_item_id' => 'integer',
        'uploaded_by' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function materialReceived(): BelongsTo
    {
        return $this->belongsTo(
            MaterialReceived::class,
            'material_received_id'
        );
    }

    public function materialReceivedItem(): BelongsTo
    {
        return $this->belongsTo(
            MaterialReceivedItem::class,
            'material_received_item_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFileUrlAttribute(): string
    {
        return asset(
            'storage/' . ltrim($this->file_path, '/')
        );
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $size = (int) ($this->file_size ?? 0);

        if ($size >= 1024 * 1024) {
            return number_format(
                $size / 1024 / 1024,
                2
            ) . ' MB';
        }

        if ($size >= 1024) {
            return number_format(
                $size / 1024,
                2
            ) . ' KB';
        }

        return $size . ' Bytes';
    }

    public function getIsReceiptLevelAttribute(): bool
    {
        return empty(
            $this->material_received_item_id
        );
    }

    public function getIsItemLevelAttribute(): bool
    {
        return ! empty(
            $this->material_received_item_id
        );
    }

    public function getMaterialNameAttribute(): ?string
    {
        return $this
            ->materialReceivedItem
            ?->materialType
            ?->material_type_name;
    }

    public function getDisplayCaptionAttribute(): string
    {
        if (! empty($this->caption)) {
            return $this->caption;
        }

        if ($this->material_name) {
            return $this->photo_type
                . ' - '
                . $this->material_name;
        }

        return $this->photo_type;
    }
}