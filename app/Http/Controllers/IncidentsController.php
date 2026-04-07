<?php

namespace App\Http\Controllers;

use App\CallType;
use App\Condition;
use App\Events\IncidentCardViewed;
use App\Events\IncidentCreated;
use App\Events\IncidentUpdated;
use App\Http\Requests\IncidentRequest;
use App\Models\CauseOfTheFire;
use App\Models\FireReport;
use App\Models\FireReportType;
use App\Models\Incident;
use App\Models\UrbanObject;
use App\Models\UrbanObjectType;
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

        $incident->load(['user', 'type', 'fireReport']);
        $incident->created_at_dt = [
            'date' => $incident->created_at->format('Y-m-d'),
            'time' => $incident->created_at->format('H:i:s'),
        ];

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
        event(new IncidentUpdated($incident));


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
                                $incident->fireReport()->create($request->fireReport);
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

            ->when($request->call_type_id, fn($q, $id) => $q->where('call_type_id', $id))
            ->when($request->conditions, fn($q, $cond) => $q->whereIn('condition', (array)$cond))
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
