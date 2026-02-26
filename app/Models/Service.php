<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public $timestamps = false;
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function callType()
    {
        return $this->belongsTo(CallType::class);
    }

}
