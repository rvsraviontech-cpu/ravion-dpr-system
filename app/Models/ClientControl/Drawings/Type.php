<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Type extends Model {
 protected $table = 'client_drawing_types';
 protected $guarded = ['*'];
 public function category() { return $this->belongsTo(Category::class, 'category_id'); }
}
