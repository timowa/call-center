<?php
namespace App\Models;
use App\Condition;
use Illuminate\Database\Eloquent\Model;
class GasReport extends Model
{
    protected $fillable = ['message', 'utility_id', 'event_id', 'instruction', 'departed_at', 'arrived_at', 'measures'];
    protected $casts = ['condition' => Condition::class];
    public function incident() { return $this->belongsTo(Incident::class); }
}
