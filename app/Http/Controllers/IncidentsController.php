<?php

namespace App\Http\Controllers;

use App\Events\FireReportUpdated;
use App\Events\IncidentCardViewed;
use App\Events\IncidentCreated;
use App\Events\IncidentUpdated;
use App\Http\Requests\IncidentRequest;
use App\Models\FireReport;
use App\Models\Incident;
use App\Service;
use App\SourceType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class IncidentsController extends Controller
{

    private function create(SourceType $sourceType)
    {
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
        event(new IncidentCardViewed($incident));

        $incident->load(['user', 'type']);
        $incident->created_at_dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];
        $services = [];
        if (!is_null($incident->fireReport)) {
            $services[] = Service::FIREFIGHTERS;
            $departments = $incident->fireReport->departments->map(function ($department) use ($incident) {
                return [
                    'department_id' => $department->id,
                    'department_name' => $department->name,
                    'operator_name' => $incident->user->name,
                    'condition_id' => $department->pivot->condition_id,
                    'datetime' => $department->pivot->updated_at->format('Y-m-d H:i:s'),
                ];
            });
            $incident->fireReport->setRelation('departments', $departments);
        }
        if (!is_null($incident->eddsReport)) {
            $services[] = Service::EDDS;
        }
        $incident->services = $services;


        $incident->processingTime = 129;

        return Inertia::render('Incidents/Edit', [
            'incident'       => $incident,
            'mode'          => $mode,
        ]);
    }

    public function view(int $id)
    {
        return $this->edit($id, true);
    }

    public function update(IncidentRequest $request, int $id)
    {
        $incident = Incident::findOrFail($id);

        $incident->update($request->all());


        $fireReportData = $request->fireReport;
        $fireReportData['incident_id'] = $incident->id;
        $fireReportSearch = [];
        if (isset($fireReportData['id'])) {
            $fireReportSearch['id'] = $fireReportData['id'];
        } else {
            $fireReportSearch['incident_id'] = $incident->id;;
        }
        $fireReport = FireReport::updateOrCreate($fireReportSearch, $fireReportData);

        if ($request->has('fireReport.departments')) {
            $departmentsData = [];
            $userId = auth()->id();

            foreach ($request->input('fireReport.departments') as $dept) {
                $departmentsData[$dept['department_id']] = [
                    'condition_id' => $dept['condition_id'],
                    'user_id'      => $userId,
                ];
            }

            $fireReport->departments()->sync($departmentsData);
        }
        event(new FireReportUpdated($fireReport));
        event(new IncidentUpdated($incident));

        return redirect()->route('dashboard')->with([
            'message' => 'Карточка успешно обновлена',
            'type' => 'success'
        ]);
    }

    public function store(IncidentRequest $request)
    {
        try {
            $incident = DB::transaction(function () use ($request) {
                $incident = new Incident();
                $incident->fill($request->all());
                $incident->user()->associate(Auth::user());
                $incident->save();

                if ($request->filled('services')) {
                    foreach ($request->services as $service_id) {
                        $service = Service::from($service_id);
                        if ($service->hasRelatedModel()) {
                            if ($service === Service::FIREFIGHTERS) {
                                $fireReport = $incident->fireReport()->create($request->fireReport);
                                if ($request->filled('fireReport.departments')) {
                                    $departments = [];
                                    $userId = auth()->id();
                                    foreach ($request->fireReport['departments'] as $department) {
                                        $departments[$department['department_id']] = [
                                            'condition_id' => $department['condition_id'],
                                            'user_id' => $userId,
                                        ];
                                    }
                                    $fireReport->departments()->sync($departments);
                                }
                            }
                            if ($service === Service::EDDS) {
                                $incident->eddsReport()->create($request->eddsReport);
                            }
                        }
                    }
                }
                return $incident;
            });
            $actionType = $request->actionType;
            if (is_array($actionType)) {
                $actionType = null;
            }
            event(new IncidentCreated($incident, $actionType));

            return redirect()->route('dashboard')->with([
                'message' => 'Карточка успешно создана',
                'type' => 'success'
            ]);
        } catch (Exception $e) {
            dd($e);
            Log::error("Ошибка создания инцидента: " . $e->getMessage());

            return redirect()->back()->withErrors([
                'error' => 'Системная ошибка при сохранении. Попробуйте позже.'
            ]);
        }
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
