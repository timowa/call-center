<?php

namespace App\Http\Controllers;

use App\Condition;
use App\Events\IncidentCardViewed;
use App\Events\IncidentCreated;
use App\Events\IncidentUpdated;
use App\Models\CauseOfTheFire;
use App\Models\FireReport;
use App\Models\FireReportType;
use App\Models\Incident;
use App\Models\UrbanObject;
use App\Models\UrbanObjectType;
use App\SourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IncidentsController extends Controller
{

    private function create(SourceType $sourceType)
    {
        $incident = Incident::create([
            'user_id' => Auth::id(),
            'incoming_number' => fake()->numerify('7##########'),
            'source_id' => $sourceType
        ]);
        if (Auth::user()->hasRole('op_01')) {
            FireReport::create([
                'incident_id' => $incident->id,
            ]);
        }
        event(new IncidentCreated($incident));
        return redirect()->route('incidents.edit', ['id' => $incident->id])->with('isNewIncident', true);
    }

    public function createInstant()
    {
        return $this->create(SourceType::INSTANT);
    }

    public function createFromCall()
    {
        return $this->create(SourceType::CALL);
    }

    public function edit(int $id)
    {
        $incident = Incident::findOrFail($id);
        event(new IncidentCardViewed($incident));
        $incident->load(['user', 'type', 'services', 'callType', 'fireReport']);
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
            'fireReportData' => $fireReportData,
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

        $data = $request->except(['services', 'created_at', 'creator', 'call_type', 'incident_type', 'area_id', 'processing_time', 'coordinates', 'fireReport', 'action']);
        $data['call_type_id'] = $request->call_type ?: null;
        $data['incident_type_id'] = $request->incident_type ?: null;

        if ($request->action === 'complete') {
            $data['condition'] = Condition::DONE;
        }

        $incident->update($data);
        event(new IncidentUpdated($incident));
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

    public function dashboard(Request $request)
    {
        $incidents = Incident::scopeArea()->select(['id','created_at', 'user_id', 'call_type_id', 'incoming_number', 'district_id','condition'])
            ->with([
                'user:id,name',
                'callType:id,name,service_id',
                'district:id,name',
                'fireReport:id,condition,incident_id',
                'services:id,name'
            ])
            ->when($request->service_id, function ($query, $serviceId) {
                return $query->whereHas('services', function ($q) use ($serviceId) {
                    $q->where('services.id', $serviceId);
                });
            })
            ->when($request->call_type_id, fn($q, $id) => $q->where('call_type_id', $id))
            ->when($request->is_training, fn($q, $val) => $q->where('is_training', $val))
            ->when($request->is_important, fn($q, $val) => $q->where('is_important', $val))
            ->when($request->emergency_threat, fn($q, $val) => $q->where('emergency_threat', $val))
            ->when($request->conditions, fn($q, $cond) => $q->whereIn('condition', (array)$cond))

            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();


        $incidents->getCollection()->transform(function ($incident) {
            return [
                'id' => $incident->id,
                'datetime' => $incident->created_at->toDateTimeString(),
                'creator' => $incident->user->name ?? 'Система',
                'operator' => '33 22 11',
                'condition' => $incident->condition,
                'call_type' => $incident->callType->name ?? '',
                'incoming_number' => $incident->incoming_number,
                'dialed_number' => '88005553535',
                'district_name' => $incident->district->name ?? 'Не указано',
                'fireReport' => $incident->fireReport,
            ];
        });
        return Inertia::render('Dashboard', ['incidents' => $incidents]);
    }
}
