<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{

   protected $guarded = [];
    protected $casts = [
        'applicant_info' => 'array',
        'services_info' => 'array',
        'is_training' => 'boolean',
        'is_important' => 'boolean',
        'emergency_threat' => 'boolean',
        'threat_to_people' => 'boolean',
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
        return $this->belongsTo(CallType::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'incident_service');
    }

    public function type()
    {
        return $this->belongsTo(IncidentType::class, 'incident_type_id', 'id', 'incident_types');
    }

    public function emergencyType()
    {
        return $this->belongsTo(EmergencyType::class);
    }


}
