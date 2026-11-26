<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Revision extends Model {
 protected $table = 'client_drawing_revisions';
 protected $guarded = ['*'];
 public function drawing() { return $this->belongsTo(Drawing::class, 'drawing_id'); }
 public function purpose() { return $this->belongsTo(Purpose::class, 'purpose_id'); }
 public function files() { return $this->hasMany(File::class, 'revision_id'); }
}
