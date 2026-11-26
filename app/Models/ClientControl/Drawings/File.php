<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class File extends Model {
 protected $table = 'client_drawing_files';
 protected $guarded = ['*'];
 public function revision() { return $this->belongsTo(Revision::class, 'revision_id'); }
}
