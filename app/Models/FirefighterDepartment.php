<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirefighterDepartment extends Model
{
    public $timestamps = false;
    protected $table = 'firefighters_departments';

    public function fireReports()
    {
        return $this->belongsToMany(FireReport::class, 'department_fire_report', 'department_id', 'fire_report_id');
    }
}
