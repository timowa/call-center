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

        $incident->load(['user', 'type', 'trainingSession']);
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
        if ($incident->gasReport) $services[] = Service::GAS;
        $incident->services = $services;


        $incident->processingTime = 129;

        return Inertia::render('Incidents/Edit', [
            'incident'       => $incident,
            'mode'          => $mode,
            'editUkio' => !$viewMode && request()->boolean('editUkio'),
            'canUpdate' => Gate::allows('update', $incident),
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
        abort_unless($request->user()->can('ukio.view'), 403);
        $request->validate([
            'incident_number' => 'nullable|integer|min:1', 'filter_incident_type' => 'nullable|integer|exists:incident_types,id',
            'incident_street' => 'nullable|string|max:255', 'comment' => 'nullable|string|max:2000',
            'applicant_street' => 'nullable|string|max:255', 'service_id' => ['nullable', \Illuminate\Validation\Rule::enum(Service::class)],
            'conditions' => 'nullable|array', 'conditions.*' => 'integer|between:1,6',
        ]);
        $available = Incident::scopeArea()->scopeUser()->get(['street', 'applicant_info']);
        $filterStreets = [
            'incident' => $available->pluck('street')->filter()->unique()->sort()->values(),
            'applicant' => $available->pluck('applicant_info.street')->filter()->unique()->sort()->values(),
        ];
        $incidents = Incident::scopeArea()->scopeUser()->select(['id','created_at', 'user_id', 'call_type_id', 'incoming_number', 'district_id','condition'])
            ->with([
                'user:id,name',
                'district:id,name',
                'fireReport:id,condition,incident_id',
                'eddsReport:id,condition,incident_id',
                'gasReport:id,condition,incident_id',
            ])
            ->when($request->service_id, function ($q, $service_id) {
                $service = Service::from($service_id);
                if ($service->hasRelatedModel()) {
                    if ($service === Service::FIREFIGHTERS) {
                        $q->whereHas('fireReport');
                    }
                    if ($service === Service::GAS) $q->whereHas('gasReport');
                    if ($service === Service::EDDS) {
                        $q->whereHas('eddsReport');
                    }
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when($request->call_type_id, fn($q, $id) => $q->where('call_type_id', $id))
            ->when($request->conditions, function($q, $cond) {
                $cond = array_map(fn ($v) => (int) $v === 1 ? 2 : (int) $v, (array) $cond);
                if (Auth::user()->hasRole('gas')) $q->whereHas('gasReport', fn ($q) => $q->whereIn('condition', $cond));
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
            ->when($request->filled('incident_number'), fn ($q) => $q->where('id', $request->integer('incident_number')))
            ->when($request->filled('filter_incident_type'), fn ($q) => $q->where('incident_type_id', $request->integer('filter_incident_type')))
            ->when($request->filled('incident_street'), fn ($q) => $q->where('street', $request->input('incident_street')))
            ->when($request->filled('applicant_street'), fn ($q) => $q->where('applicant_info->street', $request->input('applicant_street')))
            ->when($request->filled('comment'), function ($q) use ($request) {
                $text = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $request->input('comment'));
                $q->whereRaw("description LIKE ? ESCAPE '!'", ['%'.$text.'%']);
            })
            ->orderBy('created_at', 'desc')->get();


        $incidents->transform(function ($incident) {
            $condition = $incident->condition;
            if (Auth::user()->hasRole('gas')) $condition = $incident->gasReport?->condition;
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
                'gasReport' => $incident->gasReport,
            ];
        });
        return Inertia::render('Dashboard', ['incidents' => $incidents, 'filterStreets' => $filterStreets]);
    }
}
