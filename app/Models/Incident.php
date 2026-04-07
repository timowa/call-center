<?php

namespace App\Models;

use App\CallType;
use App\Condition;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class Incident extends Model
{
    protected $fillable = [
        'incident_type_id',
        'call_type_id',
        'is_training',
        'is_important',
        'district_id',
        'street',
        'house_number',
        'corpus_number',
        'apartment_number',
        'entrance_number',
        'floor',
        'number_of_floors',
        'ownership',
        'building',
        'additional_street',
        'district_of_city',
        'object',
        'longitude',
        'latitude',
        'road',
        'metre',
        'km',
        'is_nearby',
        'address_section',
        'additional_info',
        'emergency_threat',
        'threat_to_people',
        'number_of_victims',
        'emergency_type_id',
        'description',
        'applicant_info',
        'incoming_number',
    ];
   protected $guarded = [
       'condition'
   ];
    protected $casts = [
        'applicant_info' => 'array',
        'is_training' => 'boolean',
        'is_important' => 'boolean',
        'emergency_threat' => 'boolean',
        'threat_to_people' => 'boolean',
        'is_nearby' => 'boolean',
        'call_type_id' => CallType::class,
        'condition' => Condition::class,
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
    #[Scope]
    protected function scopeUser(Builder $builder):void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }
        if ($user->hasRole('op_01')) {
            $builder->whereHas('fireReport');
        }
        if ($user->hasRole('edds')) {
            $builder->whereHas('eddsReport');
        }
    }

    public function updateCondition(): void
    {
        $activeConditions = collect([
            $this->fireReport?->condition,
            $this->eddsReport?->condition,
        ])->filter();

        if ($activeConditions->isEmpty()) return;

        $newCondition = collect(Condition::cases())
            ->first(fn($case) => $activeConditions->contains($case));

        if ($newCondition && $this->condition !== $newCondition) {
            $this->condition = $newCondition;
            $this->save();
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

    public function eddsReport()
    {
        return $this->hasOne(EddsReport::class, 'incident_id', 'id');
    }


}
