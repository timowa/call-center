<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{

    protected $fillable = [
        'user_id',
    ];

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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function additionalServices()
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
