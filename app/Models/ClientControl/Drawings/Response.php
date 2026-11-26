<?php
namespace App\Models\ClientControl\Drawings;
use Illuminate\Database\Eloquent\Model;
class Response extends Model {
 protected $table = 'client_drawing_responses';
 protected $guarded = ['*'];
 public function publication() { return $this->belongsTo(Publication::class, 'publication_id'); }
}
