<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Service;
use App\Models\District;
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
        $incident = Incident::findOrFail($id)
            ->load('user')
            ->load('type')
            ->load('service');
        $incident->dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];
        $incident->additional_services = [];
        $services = Service::all();
        $incidentTypes = IncidentType::all();
        $emergencyTypes = EmergencyType::all();
        $areas = Area::all();
        $districts = District::all();
        return Inertia::render('Incidents/Edit', [
            'incident' => $incident,
            'services' => $services,
            'incidentTypes' => $incidentTypes,
            'emergencyTypes' => $emergencyTypes,
            'areas' => $areas,
            'districts' => $districts,
        ]);
    }

    public function update(Request $request, int $id)
    {
        dd($request->request->all());
    }
}
