<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\CallType;
use App\Models\EmergencyType;
use App\Models\Incident;
use App\Models\IncidentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IncidentsController extends Controller
{

    public function create()
    {
        $incident = Incident::create([
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('incidents.edit', ['id' => $incident->id]);
    }

    public function edit(int $id)
    {
        $incident = Incident::findOrFail($id)->load('user');
        $incident->dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];
        $callTypes = CallType::all();
        $incidentTypes = IncidentType::all();
        $emergencyTypes = EmergencyType::all();
        $areas = Area::all();
        return Inertia::render('Incidents/Edit', [
            'incident' => $incident,
            'callTypes' => $callTypes,
            'incidentTypes' => $incidentTypes,
            'emergencyTypes' => $emergencyTypes,
            'areas' => $areas
        ]);
    }

    public function update()
    {

    }
}
