<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Drawing extends Model {
 protected $table = 'client_drawings';
 protected $guarded = ['*'];
 public function category() { return $this->belongsTo(Category::class, 'category_id'); }
 public function type() { return $this->belongsTo(Type::class, 'type_id'); }
 public function revisions() { return $this->hasMany(Revision::class, 'drawing_id'); }
 public function publications() { return $this->hasMany(Publication::class, 'drawing_id'); }
 public function project() { return $this->belongsTo(\App\Models\Project::class, 'project_id'); }
}
