<?php

namespace App\Http\Controllers;

use App\Events\IncidentCardViewed;
use App\Http\Requests\IncidentRequest;
use App\Models\Incident;
use App\Services\DepartmentStages;
use App\Services\SaveIncident;
use App\Service;
use App\SourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class IncidentsController extends Controller
{

    private function create(SourceType $sourceType)
    {
        Gate::authorize('create', Incident::class);
        $incident = new Incident();
        $incident->source_id = $sourceType;

        if ($sourceType === SourceType::CALL) {
            $incident->incoming_number = fake()->numerify('7##########');
        }

        return Inertia::render('Incidents/Edit', ['incident' => $incident, 'sourceType' => $sourceType, 'mode' => 'create']);
    }

    public function createInstant()
    {
        return $this->create(SourceType::INSTANT);
    }

    public function createFromCall()
    {
        return $this->create(SourceType::CALL);
    }

    public function edit(int $id, bool $viewMode = false)
    {
        $mode = 'edit';
        if ($viewMode) {
            $mode = 'view';
        }
        $incident = Incident::findOrFail($id);
        Gate::authorize($viewMode ? 'view' : 'update', $incident);
        event(new IncidentCardViewed($incident));

        $incident->load(['user', 'type']);
        $incident->created_at_dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];
        $services = [];
        if (!is_null($incident->fireReport)) {
            $services[] = Service::FIREFIGHTERS;
            $departments = app(DepartmentStages::class)->rows($incident->fireReport);
            $incident->fireReport->setRelation('departments', collect($departments));
        }
        if (!is_null($incident->eddsReport)) {
            $services[] = Service::EDDS;
        }
        $incident->services = $services;


        $incident->processingTime = 129;

        return Inertia::render('Incidents/Edit', [
            'incident'       => $incident,
            'mode'          => $mode,
            'canUpdatePhone' => !$viewMode && Gate::allows('updatePhone', $incident),
        ]);
    }

    public function view(int $id)
    {
        return $this->edit($id, true);
    }

    public function update(IncidentRequest $request, int $id)
    {
        app(SaveIncident::class)->save($request, $id);
        return redirect()->route('dashboard')->with(['message' => 'Карточка успешно обновлена', 'type' => 'success']);
    }

    public function store(IncidentRequest $request)
    {
        app(SaveIncident::class)->save($request);
        return redirect()->route('dashboard')->with(['message' => 'Карточка успешно создана', 'type' => 'success']);
    }

    public function dashboard(Request $request)
    {
        $incidents = Incident::scopeArea()->scopeUser()->select(['id','created_at', 'user_id', 'call_type_id', 'incoming_number', 'district_id','condition'])
            ->with([
                'user:id,name',
                'district:id,name',
                'fireReport:id,condition,incident_id',
                'eddsReport:id,condition,incident_id',
            ])
            ->when($request->service_id, function ($q, $service_id) {
                $service = Service::from($service_id);
                if ($service->hasRelatedModel()) {
                    if ($service === Service::FIREFIGHTERS) {
                        $q->whereHas('fireReport');
                    }
                    if ($service === Service::EDDS) {
                        $q->whereHas('eddsReport');
                    }
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when($request->call_type_id, fn($q, $id) => $q->where('call_type_id', $id))
            ->when($request->conditions, function($q, $cond) {
                if (Auth::user()->hasRole('cov_112')) {
                    $q->whereIn('condition', (array)$cond);
                }
                if (Auth::user()->hasRole('op_01')) {
                    $q->whereHas('fireReport', fn ($q, ) => $q->whereIn('condition', (array)$cond));
                }
                if (Auth::user()->hasRole('edds')) {
                    $q->whereHas('eddsReport', fn ($q, ) => $q->whereIn('condition', (array)$cond));
                }
            })
            ->orderBy('created_at', 'desc')->get();


        $incidents->transform(function ($incident) {
            $condition = $incident->condition;
            if (Auth::user()->hasRole('op_01')) {
                $condition = $incident->fireReport?->condition;
            }
            if (Auth::user()->hasRole('edds')) {
                $condition = $incident->eddsReport?->condition;
            }
            return [
                'id' => $incident->id,
                'datetime' => $incident->created_at->toDateTimeString(),
                'creator' => $incident->user->name ?? 'Система',
                'operator' => '33 22 11',
                'condition' => $condition,
                'call_type' => $incident->call_type_id->label(),
                'incoming_number' => $incident->incoming_number,
                'dialed_number' => '88005553535',
                'district_name' => $incident->district->name ?? 'Не указано',
                'fireReport' => $incident->fireReport,
                'eddsReport' => $incident->eddsReport,
            ];
        });
        return Inertia::render('Dashboard', ['incidents' => $incidents]);
    }
}
