<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public $timestamps = false;
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
