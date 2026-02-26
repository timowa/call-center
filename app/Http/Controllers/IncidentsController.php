<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\CallType;
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
            ->load('callType');
        $incident->dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];
        $incident->services = [];
        $services = Service::all();
        $callTypes = CallType::all();
        $incidentTypes = IncidentType::all();
        $emergencyTypes = EmergencyType::all();
        $areas = Area::all();
        $districts = District::all();
        return Inertia::render('Incidents/Edit', [
            'incident' => $incident,
            'callTypes' => $callTypes,
            'services' => $services,
            'incidentTypes' => $incidentTypes,
            'emergencyTypes' => $emergencyTypes,
            'areas' => $areas,
            'districts' => $districts,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'call_type' => 'required',
        ],
            [
                'call_type.required' => 'Пожалуйста, выберите основную службу.'
            ]);
        $incident = Incident::findOrFail($id);
        $data = $request->except(['services', 'created_at', 'creator', 'call_type', 'incident_type', 'source', 'area_id']);
        $data['service_id'] = $request->call_type;
        $data['incident_type_id'] = $request->incident_type;
        $incident->update($data);
        $incident->services()->sync($request->services ?? []);
        return redirect()->route('dashboard')->with([
            'message' => 'Карточка успешно добавлена',
            'type' => 'success'
        ]);
    }

    public function dashboard()
    {
        $incidents = Incident::select(['id','created_at', 'user_id', 'call_type_id', 'applicant_info->phone as applicant_phone', 'district_id'])
            ->with(['user' => function($query) {
                $query->select('id', 'name');
            },
                'callType' => function($query) {
                $query->select('id', 'name', 'service_id');
                },
                'district' => function($query) {
                $query->select('id', 'name');
                }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'datetime' => $incident->created_at->format('Y-m-d H:i:s'),
                    'creator' => $incident->user->name,
                    'operator' => '33 22 11',
                    'status' => 1,
                    'call_type' => $incident->callType->name ?? '',
                    'applicant_phone' => $incident->applicant_phone,
                    'dialed_number' => '88005553535',
                    'district_name' => $incident->district->name ?? 'Не указано',
                ];
            });
        return Inertia::render('Dashboard', ['incidents' => $incidents]);
    }
}
