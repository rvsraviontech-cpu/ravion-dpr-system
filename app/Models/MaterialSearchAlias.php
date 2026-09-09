<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MaterialSearchAlias extends Model
{
    protected $fillable=['material_type_id','material_variant_id','alias','normalized_alias','sort_order','is_active','source','remarks'];
    protected $casts=['material_type_id'=>'integer','material_variant_id'=>'integer','sort_order'=>'integer','is_active'=>'boolean'];
    protected static function booted(): void { static::saving(function(self $alias): void { if (filled($alias->alias)) { $alias->normalized_alias=static::normalizeAlias((string)$alias->alias); } }); }
    public function product(): BelongsTo { return $this->belongsTo(MaterialType::class,'material_type_id'); }
    public function materialType(): BelongsTo { return $this->product(); }
    public function variant(): BelongsTo { return $this->belongsTo(MaterialVariant::class,'material_variant_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopeForSearch(Builder $query,string $term): Builder { return $query->where('normalized_alias','like','%'.static::normalizeAlias($term).'%'); }
    public static function normalizeAlias(string $value): string { $value=Str::lower(trim($value)); $value=str_replace(['–','—','−','/','\\','&','+'],['-','-','-',' ',' ',' and ',' '],$value); return trim(preg_replace('/[^a-z0-9]+/u',' ',$value) ?? $value); }
}
