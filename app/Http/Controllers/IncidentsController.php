<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\CallType;
use App\Models\CauseOfTheFire;
use App\Models\FireReport;
use App\Models\FireReportType;
use App\Models\Service;
use App\Models\District;
use App\Models\EmergencyType;
use App\Models\Incident;
use App\Models\IncidentType;
use App\Models\UrbanObject;
use App\Models\UrbanObjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IncidentsController extends Controller
{

    public function create()
    {
        $incident = Incident::create([
            'user_id' => Auth::id(),
            'incoming_number' => fake()->numerify('7##########'),
            'condition' => 1
        ]);
        if (Auth::user()->hasRole('op_01')) {
            FireReport::create([
                'incident_id' => $incident->id,
                'condition' => 1
            ]);
        }
        return redirect()->route('incidents.edit', ['id' => $incident->id]);
    }

    public function edit(int $id)
    {
        return $this->renderIncidentForm($id, false);
    }

    public function view(int $id)
    {
        return $this->renderIncidentForm($id, true);
    }

    private function renderIncidentForm(int $id, bool $viewMode)
    {
        $incident = Incident::findOrFail($id);
        $incident->loadMissing(['user', 'type', 'services', 'callType', 'fireReport']);
        $incident->dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];

        $incident->processingTime = 129;

        $fireReportData = [
            'report_types' => FireReportType::all(),
            'types_of_fire_protection' => FireReport::getTypesOfProtection(),
            'objects' => UrbanObject::all(),
            'object_types' => UrbanObjectType::all(),
            'water_sources' => FireReport::getWaterSources(),
            'causes'=> CauseOfTheFire::all(),
            'ranks' => FireReport::getRanks(),
            'plans' => FireReport::getPlans()
        ];
        return Inertia::render('Incidents/Edit', [
            'incident'       => $incident,
            'viewMode'       => $viewMode,
            'services'       => Service::all(),
            'callTypes'      => CallType::all(),
            'incidentTypes'  => IncidentType::all(),
            'emergencyTypes' => EmergencyType::all(),
            'areas'          => Area::all(),
            'districts'      => District::all(),
            'fireReportData' => $fireReportData,
            'isCreator' => $incident->user->id === Auth::id() || Auth::user()->hasRole('cov_112'),
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
        $data = $request->except(['services', 'created_at', 'creator', 'call_type', 'incident_type', 'area_id', 'processing_time', 'coordinates', 'fireReport']);
        $data['call_type_id'] = $request->call_type ?: null;
        $data['incident_type_id'] = $request->incident_type ?: null;
        $incident->update($data);
        $incident->services()->syncWithoutDetaching(array_column($request->services, 'id') ?? []);

        $fireReportData = $request->fireReport;
        $fireReportData['incident_id'] = $incident->id;
        $fireReportSearch = [];
        if (isset($fireReportData['id'])) {
            $fireReportSearch['id'] = $fireReportData['id'];
        } else {
            $fireReportSearch['incident_id'] = $incident->id;;
        }
        FireReport::updateOrCreate($fireReportSearch, $fireReportData);
        return redirect()->route('dashboard')->with([
            'message' => 'Карточка успешно добавлена',
            'type' => 'success'
        ]);
    }

    public function dashboard()
    {
        $incidents = Incident::scopeArea()->select(['id','created_at', 'user_id', 'call_type_id', 'incoming_number', 'district_id','condition'])
            ->with([
                'user' => function($query) {
                    $query->select('id', 'name');
                    },
                'callType' => function($query) {
                    $query->select('id', 'name', 'service_id');
                    },
                'district' => function($query) {
                    $query->select('id', 'name');
                    },
                'fireReport' => function($query) {
                    $query->select('id', 'condition', 'incident_id');
                    }
                    ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'datetime' => $incident->created_at->format('Y-m-d H:i:s'),
                    'creator' => $incident->user->name,
                    'operator' => '33 22 11',
                    'condition' => $incident->condition,
                    'call_type' => $incident->callType->name ?? '',
                    'incoming_number' => $incident->incoming_number,
                    'dialed_number' => '88005553535',
                    'district_name' => $incident->district->name ?? 'Не указано',
                    'fireReport' => $incident->fireReport ?? null,
                ];
            });
        return Inertia::render('Dashboard', ['incidents' => $incidents]);
    }
}
