<?php

namespace App\Models;

use App\Condition;
use Illuminate\Database\Eloquent\Model;

class FireReport extends Model
{
    protected $fillable = [
        'report_type_id',
        'is_error_card',
        'type_of_fire_protection',
        'object_id',
        'object_type_id',
        'plan',
        'rank',
        'card_number',
        'shift',
        'water_consumption',
        'dead_total',
        'dead_children',
        'dead_staff',
        'injured_children',
        'injured_staff',
        'rescued_children',
        'rescued_staff',
        'violated_children',
        'violated_staff',
        'info',
        'barrels',
        'fire_extinguishing_agents',
        'rtp',
        'personnel',
        'gdzs',
        'cause_id',
        'localized_at',
        'fire_elimination_at',
        'elimination_at',
        'viewed_at',
    ];
    protected $casts = [
        'info' => 'array',
        'barrels' => 'array',
        'fire_extinguishing_agents' => 'array',
        'rtp' => 'array',
        'personnel' => 'array',
        'gdzs' => 'array',
        'is_error_card' => 'boolean',
        'condition' => Condition::class,
    ];
    protected $guarded = ['condition'];

    public static function getTypesOfProtection()
    {
        return collect([
            ['id' => 1, 'name' => 'Ведомственная'],
            ['id' => 2, 'name' => 'ВПО'],
            ['id' => 3, 'name' => 'ДПО'],
            ['id' => 4, 'name' => 'МПО'],
        ]);
    }


    public static function getWaterSources()
    {
        return collect([
            ['id' => 1, 'name' => 'Без установки водоисточника'],
            ['id' => 2, 'name' => 'Водоем естественный'],
            ['id' => 3, 'name' => 'Водоем искусственный'],
            ['id' => 4, 'name' => '3Наружный противопож. воддопровод'],
        ]);
    }

    public static function getRanks()
    {
        return collect([
            ['id' => 1, 'name' => '1'],
            ['id' => 2, 'name' => '1 бис'],
            ['id' => 3, 'name' => '2'],
            ['id' => 4, 'name' => '3'],
        ]);
    }

    public static function getPlans()
    {
        return collect([
            ['id' => 1, 'name' => 'Первый'],
            ['id' => 2, 'name' => 'Второй'],
            ['id' => 3, 'name' => 'Третий'],
            ['id' => 4, 'name' => 'Четвертый'],
        ]);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function type()
    {
        return $this->belongsTo(FireReportType::class);
    }

    public function object()
    {
        return $this->belongsTo(UrbanObject::class, 'object_id', 'id');
    }

    public function objectType()
    {
        return $this->belongsTo(UrbanObjectType::class, 'object_type_id', 'id');
    }

    public function cause()
    {
        return $this->belongsTo(CauseOfTheFire::class, 'cause_id', 'id');
    }
}
