<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Sequence extends Model {
 protected $table = 'client_drawing_sequences';
 protected $guarded = ['*'];
 public function category() { return $this->belongsTo(Category::class, 'category_id'); }
}
