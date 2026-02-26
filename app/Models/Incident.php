<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{

   protected $guarded = [];
    protected $casts = [
        'applicant_info' => 'array',
        'services_info' => 'array',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function callType()
    {
        return $this->belongsTo(Service::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function type()
    {
        return $this->belongsTo(IncidentType::class);
    }

    public function emergencyType()
    {
        return $this->belongsTo(EmergencyType::class);
    }


}
