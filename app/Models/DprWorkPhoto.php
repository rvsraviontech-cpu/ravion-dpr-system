<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DprWorkPhoto extends Model
{
    protected $fillable = [
        'dpr_work_item_id',
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
        'dpr_work_item_id' => 'integer',
        'uploaded_by' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(
            DprWorkItem::class,
            'dpr_work_item_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

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

    public function getDisplayCaptionAttribute(): string
    {
        if (! empty($this->caption)) {
            return $this->caption;
        }

        return $this->photo_type ?: 'Work Photo';
    }
}