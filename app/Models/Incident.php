<?php

namespace App\Models;

use App\Models\Scopes\ScopeArea;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
        'is_nearby' => 'boolean'
    ];
    #[Scope]
    protected function scopeArea(Builder $builder):void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }
        if ($user->area_id && $user->hasRole('cov_112')) {
            $builder->whereHas('district', function ($query) use ($user) {
                $query->where('area_id', $user->area_id);
            })
            ->orWhere('user_id', $user->id);
        }
    }



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

    public function fireReport()
    {
        return $this->hasOne(FireReport::class, 'incident_id', 'id');
    }


}
