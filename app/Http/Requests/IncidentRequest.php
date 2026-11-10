<?php

namespace App\Http\Requests;

use App\CallType;
use App\Service;
use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncidentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $report = $this->input('fireReport');
        if (!is_array($report)) return;
        // Earlier datetime-local controls stored JSON dates with T and optional seconds.
        $normalize = function ($value) {
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $value)) {
                return str_replace('T', ' ', $value).(strlen($value) === 16 ? ':00' : '');
            }
            return $value;
        };
        foreach (['localized_at', 'fire_eliminated_at', 'elimination_at'] as $field) {
            if (array_key_exists($field, $report)) $report[$field] = $normalize($report[$field]);
        }
        foreach (['barrels' => 'time', 'fire_extinguishing_agents' => 'time', 'rtp' => 'datetime', 'gdzs' => 'datetime'] as $table => $field) {
            if (!isset($report[$table]) || !is_array($report[$table])) continue;
            foreach ($report[$table] as &$row) {
                if (is_array($row) && array_key_exists($field, $row)) $row[$field] = $normalize($row[$field]);
            }
            unset($row);
        }
        $this->merge(['fireReport' => $report]);
    }

    public function authorize(): bool
    {
        return $this->route('id')
            ? $this->user()->can('update', Incident::findOrFail($this->route('id')))
            : $this->user()->can('create', Incident::class);
    }

    public function rules(): array
    {
        $rules = [
            'applicant_info' => ['sometimes', 'nullable', 'array'],
            'applicant_info.phone' => ['sometimes', 'nullable', 'string'],
            'call_type_id' => ['required', Rule::enum(CallType::class)],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'incoming_number' => ['required_without:id'],
            'services' => ['sometimes', 'array'],
            'services.*' => ['integer', 'distinct', Rule::enum(Service::class)],
            'fireReport' => ['sometimes', 'nullable', 'array'],
            'eddsReport' => ['sometimes', 'nullable', 'array'],
            'fireReport.id' => ['nullable', 'integer'],
            'eddsReport.id' => ['nullable', 'integer'],
            'fireReport.departments' => ['sometimes', 'array'],
            'fireReport.departments.*.department_id' => ['required', 'integer', 'distinct', 'exists:firefighters_departments,id'],
            'fireReport.departments.*.condition_id' => ['required', 'integer', 'between:1,4'],
            'fireReport.departments.*.transitions' => ['sometimes', 'array'],
            'fireReport.departments.*.transitions.*' => ['integer', 'between:2,4'],
        ];
        // Wall-clock values use config(app.timezone), not the browser timezone.
        foreach (['localized_at', 'fire_eliminated_at', 'elimination_at', 'departments.*.requested_at', 'departments.*.accepted_at', 'departments.*.arrived_at', 'departments.*.completed_at', 'barrels.*.time', 'fire_extinguishing_agents.*.time', 'rtp.*.datetime', 'gdzs.*.datetime'] as $field) {
            $rules['fireReport.'.$field] = ['nullable', 'date_format:Y-m-d H:i:s'];
        }
        foreach (['barrels', 'fire_extinguishing_agents', 'rtp', 'personnel', 'gdzs', 'info'] as $field) {
            $rules['fireReport.'.$field] = ['sometimes', 'nullable', 'array'];
        }
        return $rules;
    }

    public function messages(): array
    {
        return ['date_format' => 'Укажите корректную дату и время.', 'distinct' => 'Отделение уже добавлено.', 'required' => 'Не заполнены обязательные поля.'];
    }
}
