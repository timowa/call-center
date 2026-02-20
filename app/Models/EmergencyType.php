<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyType extends Model
{
    public $timestamps = false;
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
