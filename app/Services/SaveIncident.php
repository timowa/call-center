<?php

namespace App\Services;

use App\Events\{FireReportUpdated, IncidentCreated, IncidentUpdated};
use App\Http\Requests\IncidentRequest;
use App\Models\{District, Incident};
use App\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveIncident
{
    private function comparable(mixed $value): mixed
    {
        if (is_array($value)) {
            $value = array_filter(array_map(fn ($v) => $this->comparable($v), $value), fn ($v) => $v !== null);
            ksort($value);
            return $value ?: null;
        }
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $value)) {
            return str_replace('T', ' ', $value).(strlen($value) === 16 ? ':00' : '');
        }
        return $value === '' || $value === false ? null : $value;
    }

    public function save(IncidentRequest $request, ?int $id = null): Incident
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();
            $training = null;
            if (!$id && $request->filled('training_session_id')) {
                $training = \App\Models\TrainingSession::lockForUpdate()->findOrFail($request->integer('training_session_id'));
                abort_unless($training->user_id === $user->id,403);
                if ($training->incident_id) return $training->incident;
            }
            $incident = $id ? Incident::lockForUpdate()->findOrFail($id) : new Incident();
            Gate::authorize($id ? 'update' : 'create', $id ? $incident : Incident::class);
            $original = clone $incident;
            $incident->fill($request->all());
            if (!$id) $incident->source_id = $request->integer('source_id', 1);
            if ($id && !$user->can('ukio.edit') && is_array($request->input('applicant_info'))) {
                $applicant = $original->applicant_info ?? [];
                foreach ($request->input('applicant_info') as $key => $value) {
                    if ($key !== 'phone') {
                        abort_unless($this->comparable($value) == $this->comparable($applicant[$key] ?? null), 403);
                    }
                }
                if (array_key_exists('phone', $request->input('applicant_info'))) {
                    $applicant['phone'] = $request->input('applicant_info.phone');
                }
                $incident->applicant_info = $applicant;
            }
            if ($id && !$user->can('ukio.edit')) {
                foreach ($incident->getDirty() as $field => $value) {
                    if ($this->comparable($incident->$field) == $this->comparable($original->$field)) continue;
                    if ($field === 'applicant_info' && is_array($request->input('applicant_info')) && Gate::allows('updatePhone', $original)) continue;
                    abort_unless($field === 'description' && $user->can('ukio.edit-description'), 403);
                }
            }
            if ($user->area_id && (!$id || $incident->isDirty('district_id'))) {
                abort_unless((int) District::find($incident->district_id)?->area_id === (int) $user->area_id, 403);
            }
            if (!$id) $incident->user()->associate($user);
            $incident->save();
            if ($training) {
                $incident->source_id = match ($training->kind) { 'call' => 2, 'sms' => 3, 'glonass' => 4 };
                $incident->save();
                $training->incident_id = $incident->id; $training->save();
            }
            $stagesChanged = false;
            foreach (['fireReport' => ['01', Service::FIREFIGHTERS], 'eddsReport' => ['edds', Service::EDDS], 'gasReport' => ['gas', Service::GAS]] as $relation => [$permission, $service]) {
                $report = $incident->$relation;
                $data = $request->input($relation);
                if (!is_array($data)) {
                    if (!$report && in_array($service->value, $request->input('services', []))) $data = [];
                    else continue;
                }
                if (!empty($data['id']) && (!$report || (int) $data['id'] !== $report->id)) {
                    throw ValidationException::withMessages([$relation.'.id' => 'Отчёт не принадлежит этой карточке.']);
                }
                if (!$report && !in_array($service->value, $request->input('services', []))) {
                    if (!empty($data['departments'])) {
                        throw ValidationException::withMessages([$relation => 'Сначала подключите службу.']);
                    }
                    continue;
                }
                if (!$report) {
                    abort_unless($user->can($permission.'.create'), 403);
                    $report = $incident->$relation()->create($data);
                } else {
                    $candidate = clone $report;
                    $candidate->fill($data);
                    $changed = collect($candidate->getDirty())->keys()->contains(fn ($field) => $this->comparable($candidate->$field) != $this->comparable($report->$field));
                    if ($changed) {
                        abort_unless($user->can($permission.'.edit'), 403);
                        $candidate->save();
                        $report = $candidate;
                    }
                }
                if ($relation === 'fireReport' && array_key_exists('departments', $data)) {
                    $stagesChanged = app(DepartmentStages::class)->save($report, $data['departments'], $user);
                }
            }
            $incident->unsetRelations();
            $complete = $request->input('complete_service');
            if (!$id) {
                $action = $request->input('actionType');
                event(new IncidentCreated($incident, is_string($action) && $action !== '' ? $action : null));
            }
            if ($stagesChanged) {
                event(new FireReportUpdated($incident->fireReport));
                $incident->unsetRelations();
                // Mixed-service aggregation remains unchanged pending customer agreement.
                if (!$incident->eddsReport()->exists() && !$incident->gasReport()->exists()) {
                    $incident->condition = $incident->fireReport->condition;
                    $incident->save();
                }
            }
            if ($complete) {
                $relations = ['01' => 'fireReport', 'edds' => 'eddsReport', 'gas' => 'gasReport'];
                abort_unless($id && isset($relations[$complete]) && $user->can($complete.'.edit'), 403);
                $report = $incident->{$relations[$complete]};
                abort_unless($report, 422);
                $report->condition = \App\Condition::DONE;
                $report->save();
                $incident->unsetRelations();
            }
            if ($id && ($stagesChanged || $complete)) event(new IncidentUpdated($incident));
            return $incident;
        });
    }
}
