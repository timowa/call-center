<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingSession extends Model {
 protected $guarded = ['id'];
 protected $casts = ['messages' => 'array', 'hung_up' => 'boolean'];
 public function incident() { return $this->belongsTo(Incident::class); }
}
