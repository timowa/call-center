<?php

namespace App\Http\Controllers;

use App\Condition;
use App\Events\{FireReportUpdated, IncidentUpdated};
use App\Models\Incident;
use App\Services\DepartmentStages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Gate};

class DepartmentTransitionController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $data = $request->validate([
            'report_id' => 'required|integer', 'department_id' => 'required|integer|exists:firefighters_departments,id',
            'expected_condition' => 'required|integer|between:1,3',
            'condition_id' => 'required|integer|between:2,4',
        ]);
        return DB::transaction(function () use ($request, $id, $data) {
            $incident = Incident::lockForUpdate()->findOrFail($id);
            Gate::authorize('update', $incident);
            abort_unless($request->user()->can('01.edit'), 403);
            $report = $incident->fireReport;
            abort_unless($report && $report->id === $data['report_id'], 404);
            abort_if($report->condition === Condition::DONE, 409, 'Работа службы уже завершена. Обновите карточку.');
            $stages = app(DepartmentStages::class);
            $row = collect($stages->rows($report))->firstWhere('department_id', $data['department_id']);
            $row ??= ['department_id' => $data['department_id'], 'condition_id' => 1];
            abort_unless($row['condition_id'] === $data['expected_condition'], 409, 'Состояние изменено другим оператором. Обновите карточку.');
            abort_unless($data['condition_id'] === $row['condition_id'] + 1, 422, 'Допустим только следующий этап.');
            $row['condition_id'] = $data['condition_id'];
            $row['transitions'] = [$data['condition_id']];
            $row[DepartmentStages::FIELDS[$data['condition_id']]] = now()->format('Y-m-d H:i:s');
            $stages->save($report, [$row], $request->user());
            event(new FireReportUpdated($report));
            $incident->unsetRelations();
            if (!$incident->eddsReport()->exists() && !$incident->gasReport()->exists()) {
                $incident->condition = $incident->fireReport->condition;
                $incident->save();
            }
            event(new IncidentUpdated($incident));
            return response()->json([
                'row' => collect($stages->rows($report))->firstWhere('department_id', $data['department_id']),
                'condition' => $incident->fresh()->condition,
                'report_condition' => $report->fresh()->condition,
            ]);
        });
    }
}
