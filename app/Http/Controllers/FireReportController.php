<?php

namespace App\Http\Controllers;

use App\FireReportBarrelType;
use App\FireReportPlan;
use App\FireReportRank;
use App\FireReportTypeOfProtection;
use App\Models\CauseOfTheFire;
use App\Models\FireReportType;
use App\Models\FireReportWaterSource;
use App\Models\UrbanObject;
use App\Models\UrbanObjectType;

class FireReportController extends Controller
{
    public function getFireDirectories()
    {
        return response()->json([
            'report_types' => FireReportType::all(),
            'types_of_fire_protection' => collect(FireReportTypeOfProtection::cases())->map(fn($s) => [
                'id' => $s->value,
                'name' => $s->label(),
            ]),
            'objects' => UrbanObject::all(),
            'object_types' => UrbanObjectType::all(),
            'water_sources' => FireReportWaterSource::all(),
            'causes'=> CauseOfTheFire::all(),
            'ranks' => collect(FireReportRank::cases())->map(fn($s) => [
                'id' => $s->value,
                'name' => $s->label(),
            ]),
            'plans' => collect(FireReportPlan::cases())->map(fn($s) => [
                'id' => $s->value,
                'name' => $s->label(),
            ]),
            'barrel_types' => collect(FireReportBarrelType::cases())->map(fn($s) => [
                'id' => $s->value,
                'name' => $s->label(),
            ])
        ]);
    }
}
