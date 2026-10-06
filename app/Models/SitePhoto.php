<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePhoto extends Model
{
    public const TYPE_PROGRESS = 'Progress Photo';
    public const TYPE_BEFORE = 'Before Work';
    public const TYPE_DURING = 'During Work';
    public const TYPE_AFTER = 'After Work';
    public const TYPE_DETAIL = 'Detail Photo';
    public const TYPE_GENERAL = 'General Photo';

    protected $fillable = [
        'site_photo_entry_id',
        'uploaded_by',
        'photo_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'caption',
        'sort_order',
        'captured_at',
    ];

    protected $casts = [
        'site_photo_entry_id' => 'integer',
        'uploaded_by' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
        'captured_at' => 'datetime',
    ];

    public static function photoTypes(): array
    {
        return [
            self::TYPE_PROGRESS,
            self::TYPE_BEFORE,
            self::TYPE_DURING,
            self::TYPE_AFTER,
            self::TYPE_DETAIL,
            self::TYPE_GENERAL,
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(
            SitePhotoEntry::class,
            'site_photo_entry_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function getDisplayCaptionAttribute(): string
    {
        return $this->caption
            ?: $this->photo_type
            ?: 'Site Photo';
    }
}