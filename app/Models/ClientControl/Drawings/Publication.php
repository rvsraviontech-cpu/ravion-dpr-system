<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Publication extends Model {
 protected $table = 'client_drawing_publications';
 protected $guarded = ['*'];
 public function revision() { return $this->belongsTo(Revision::class, 'revision_id'); }
 public function drawing() { return $this->belongsTo(Drawing::class, 'drawing_id'); }
 public function responses() { return $this->hasMany(Response::class, 'publication_id'); }
}
