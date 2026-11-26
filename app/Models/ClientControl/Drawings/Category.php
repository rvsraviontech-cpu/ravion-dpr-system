<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Category extends Model {
 protected $table = 'client_drawing_categories';
 protected $guarded = ['*'];
 public function types() { return $this->hasMany(Type::class, 'category_id'); }
}
