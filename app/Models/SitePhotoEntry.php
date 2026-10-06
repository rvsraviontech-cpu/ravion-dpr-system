<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePhotoEntry extends Model
{
    public const CATEGORY_WORK_PROGRESS = 'Work Progress';
    public const CATEGORY_BEFORE_WORK = 'Before Work';
    public const CATEGORY_DURING_WORK = 'During Work';
    public const CATEGORY_COMPLETED_WORK = 'Completed Work';
    public const CATEGORY_GENERAL_SITE = 'General Site';
    public const CATEGORY_QUALITY = 'Quality';
    public const CATEGORY_SAFETY = 'Safety';
    public const CATEGORY_MATERIAL = 'Material';
    public const CATEGORY_MACHINERY_EQUIPMENT = 'Machinery / Equipment';
    public const CATEGORY_HOUSEKEEPING = 'Housekeeping';
    public const CATEGORY_SITE_CONDITION = 'Site Condition';
    public const CATEGORY_OTHER = 'Other';

    public const STATUS_DRAFT = 'Draft';
    public const STATUS_SUBMITTED = 'Submitted';

    protected $fillable = [
        'project_id',
        'photo_date',
        'reported_by',

        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',

        'work_package_id',
        'work_section_id',
        'work_activity_id',

        'contractor_id',

        'category',
        'title',
        'remarks',

        'status',
        'submitted_at',

        'dpr_id',
    ];

    protected $casts = [
        'photo_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public static function categories(): array
    {
        return [
            self::CATEGORY_WORK_PROGRESS,
            self::CATEGORY_BEFORE_WORK,
            self::CATEGORY_DURING_WORK,
            self::CATEGORY_COMPLETED_WORK,
            self::CATEGORY_GENERAL_SITE,
            self::CATEGORY_QUALITY,
            self::CATEGORY_SAFETY,
            self::CATEGORY_MATERIAL,
            self::CATEGORY_MACHINERY_EQUIPMENT,
            self::CATEGORY_HOUSEKEEPING,
            self::CATEGORY_SITE_CONDITION,
            self::CATEGORY_OTHER,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFloor::class,
            'project_floor_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(
            ProjectRoom::class,
            'project_room_id'
        );
    }

    public function subspace(): BelongsTo
    {
        return $this->belongsTo(
            ProjectSubspace::class,
            'project_subspace_id'
        );
    }

    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(
            WorkPackage::class,
            'work_package_id'
        );
    }

    public function workSection(): BelongsTo
    {
        return $this->belongsTo(
            WorkSection::class,
            'work_section_id'
        );
    }

    public function workActivity(): BelongsTo
    {
        return $this->belongsTo(
            WorkActivity::class,
            'work_activity_id'
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(Dpr::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(
            SitePhoto::class,
            'site_photo_entry_id'
        )->orderBy('sort_order')->orderBy('id');
    }

    public function getPhotoCountAttribute(): int
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->count();
        }

        return $this->photos()->count();
    }

    public function getLocationPathAttribute(): string
    {
        return collect([
            $this->block?->name,
            $this->floor?->name,
            $this->unit?->name,
            $this->room?->name,
            $this->subspace?->name,
        ])
            ->filter()
            ->implode(' → ');
    }

    public function getWorkPathAttribute(): string
    {
        return collect([
            $this->workPackage?->name,
            $this->workSection?->name,
            $this->workActivity?->name,
        ])
            ->filter()
            ->implode(' → ');
    }

    public function getIsSubmittedAttribute(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}