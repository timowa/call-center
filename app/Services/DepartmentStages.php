<?php

namespace App\Services;

use App\Models\FireReport;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DepartmentStages
{
    public const FIELDS = [1 => 'requested_at', 2 => 'accepted_at', 3 => 'arrived_at', 4 => 'completed_at'];

    public function save(FireReport $report, array $rows, User $user): bool
    {
        $existing = $report->departments()->get()->keyBy('id');
        $statesChanged = false;
        foreach ($rows as $index => $row) {
            $pivot = $existing->get($row['department_id'])?->pivot;
            $before = (int) ($pivot?->condition_id ?? 1);
            $after = (int) $row['condition_id'];
            $expected = $after > $before ? range($before + 1, $after) : [];
            if ($after < $before || array_map('intval', $row['transitions'] ?? []) !== $expected) {
                throw ValidationException::withMessages(["fireReport.departments.$index.condition_id" => 'Недопустимый переход. Обновите карточку и повторите последовательные шаги.']);
            }
            $values = ['condition_id' => $after, 'user_id' => $pivot?->user_id ?? $user->id];
            foreach (self::FIELDS as $step => $field) {
                $value = array_key_exists($field, $row) ? $row[$field] : $pivot?->$field;
                if (!$pivot && $step === 1 && !$value) {
                    $value = now()->format('Y-m-d H:i:s');
                }
                if (($step > $after && $value !== null) ||
                    ($step <= $after && !$value && (($pivot?->$field !== null) || in_array($step, $expected)))) {
                    throw ValidationException::withMessages(["fireReport.departments.$index.$field" => 'Укажите время состоявшегося этапа; будущие этапы должны оставаться пустыми.']);
                }
                $values[$field] = $value;
            }
            if (!$user->can('01.edit')) {
                // Creation permission connects the service, but does not grant operational actions.
                abort_unless($pivot && $after === $before && collect($values)->every(fn ($value, $key) => $value == $pivot->$key), 403);
                continue;
            }
            $statesChanged = $statesChanged || !$pivot || $before !== $after;
            if (!$pivot || collect($values)->contains(fn ($value, $key) => $value != $pivot->$key)) {
                $report->departments()->syncWithoutDetaching([$row['department_id'] => $values]);
            }
        }
        $report->unsetRelation('departments');
        return $statesChanged;
    }

    public function rows(FireReport $report): array
    {
        $departments = $report->departments;
        $users = User::whereIn('id', $departments->pluck('pivot.user_id'))->get()->keyBy('id');
        return $departments->map(function ($department) use ($users) {
            $pivot = $department->pivot;
            $operator = $users->get($pivot->user_id);
            return [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'operator_name' => $operator ? $operator->name.' ('.$operator->uid.')' : 'Не указан',
                'condition_id' => (int) $pivot->condition_id,
                ...collect(self::FIELDS)->mapWithKeys(fn ($field) => [$field => $pivot->$field])->all(),
                'transitions' => [],
            ];
        })->all();
    }
}
