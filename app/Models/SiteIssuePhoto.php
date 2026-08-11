<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteIssuePhoto extends Model
{
    protected $fillable = [
        'site_issue_id',
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
        'site_issue_id' => 'integer',
        'uploaded_by' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function siteIssue(): BelongsTo
    {
        return $this->belongsTo(
            SiteIssue::class,
            'site_issue_id'
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
        return $this->caption
            ?: ($this->photo_type ?: 'Site Issue Photo');
    }
}
