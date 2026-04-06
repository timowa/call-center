<?php

namespace App\Http\Controllers;

use App\Models\CauseOfTheFire;
use App\Models\FireReport;
use App\Models\FireReportType;
use App\Models\UrbanObject;
use App\Models\UrbanObjectType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FireReportController extends Controller
{
    public function getFireDirectories()
    {
        return response()->json([
            'report_types' => FireReportType::all(),
            'types_of_fire_protection' => FireReport::getTypesOfProtection(),
            'objects' => UrbanObject::all(),
            'object_types' => UrbanObjectType::all(),
            'water_sources' => FireReport::getWaterSources(),
            'causes'=> CauseOfTheFire::all(),
            'ranks' => FireReport::getRanks(),
            'plans' => FireReport::getPlans()
        ]);
    }
}
