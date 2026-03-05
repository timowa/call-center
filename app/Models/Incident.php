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
    protected $appends = ['condition_info'];

    CONST CONDITION_CALL = 1;
    CONST CONDITION_CONNECTING = 2;
    CONST CONDITION_RESPONSE = 3;
    CONST CONDITION_PROCESSING = 4;
    CONST CONDITION_DONE = 5;
    CONST CONDITION_VIEW = 6;
    const CONDITIONS = [
        self::CONDITION_CALL => ['name' => 'Запрос в 112', 'color' => 'red'],
        self::CONDITION_CONNECTING => ['name' => 'Подключение', 'color' => 'green'],
        self::CONDITION_RESPONSE => ['name' => 'Реагирование', 'color' => 'indigo'],
        self::CONDITION_PROCESSING => ['name' => 'В работе', 'color' => 'yellow'],
        self::CONDITION_DONE => ['name' => 'Отработана', 'color' => 'black'],
        self::CONDITION_VIEW => ['name' => 'Просмотр  ', 'color' => ''],
    ];
    #[Scope]
    protected function scopeArea(Builder $builder):void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }
        if ($user->area_id && $user->hasPermissionTo('incidents.view.own-area')) {
            $builder->whereHas('district', function ($query) use ($user) {
                $query->where('area_id', $user->area_id);
            })
            ->orWhere('user_id', $user->id);
        }
    }

    public function getConditionInfoAttribute()
    {
        return self::CONDITIONS[$this->condition] ?? ['name' => 'Неизвестно', 'color' => 'gray'];
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


}
