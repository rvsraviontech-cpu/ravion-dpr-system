<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Event extends Model {
 protected $table = 'client_drawing_events';
 protected $guarded = ['*'];
 public $timestamps = false;
 public function drawing() { return $this->belongsTo(Drawing::class, 'drawing_id'); }
 public function revision() { return $this->belongsTo(Revision::class, 'revision_id'); }
}
