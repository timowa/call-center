<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AdminDirectories
{
    public static function all(): array
    {
        return [
            'incident-types' => ['label' => 'Тип происшествия', 'groups' => ['ukio'], 'table' => 'incident_types', 'references' => [['incidents', 'incident_type_id']]],
            'districts' => ['label' => 'Населённые пункты', 'groups' => ['ukio'], 'table' => 'districts', 'area' => true, 'references' => [['incidents', 'district_id']], 'json' => [['incidents', 'applicant_info', 'area']]],
            'objects' => ['label' => 'Объекты', 'groups' => ['ukio', '01'], 'table' => 'urban_objects', 'area' => true, 'references' => [['incidents', 'object'], ['fire_reports', 'object_id']]],
            'report-types' => ['label' => 'Вид выезда', 'groups' => ['01'], 'table' => 'fire_report_types', 'references' => [['fire_reports', 'report_type_id']]],
            'object-types' => ['label' => 'Тип объекта', 'groups' => ['01'], 'table' => 'urban_object_types', 'references' => [['fire_reports', 'object_type_id']]],
            'water-sources' => ['label' => 'Водоисточник', 'groups' => ['01'], 'table' => 'fire_report_water_sources', 'references' => [['fire_reports', 'water_source_id']]],
            'fire-causes' => ['label' => 'Причина пожара', 'groups' => ['01'], 'table' => 'causes_of_the_fire', 'references' => [['fire_reports', 'cause_id']]],
            'posts' => ['label' => 'Должности', 'groups' => ['01'], 'table' => 'firefighter_posts', 'json' => [['fire_reports', 'rtp', '*.post_id'], ['fire_reports', 'personnel', '*.post_id']]],
            'extinguishing-agents' => ['label' => 'Огнетушащие средства', 'groups' => ['01'], 'table' => 'fire_report_fire_extinguishing_agents', 'json' => [['fire_reports', 'fire_extinguishing_agents', '*.type']]],
            'departments' => ['label' => 'Подразделения 01', 'groups' => ['01'], 'table' => 'firefighters_departments', 'references' => [['department_fire_report', 'department_id']]],
            'gas-utilities' => ['label' => 'Коммунальная служба', 'groups' => ['gas'], 'table' => 'gas_utilities', 'references' => [['gas_reports', 'utility_id']]],
            'gas-events' => ['label' => 'Происшествия', 'groups' => ['gas'], 'table' => 'gas_events', 'references' => [['gas_reports', 'event_id']]],
        ];
    }

    public static function get(string $key): array
    {
        abort_unless(isset(self::all()[$key]), 404);
        return self::all()[$key];
    }

    public static function isUsed(array $directory, int $id): bool
    {
        foreach ($directory['references'] ?? [] as [$table, $column]) {
            if (DB::table($table)->where($column, (string) $id)->exists()) return true;
        }
        // These references are embedded in JSON and have no foreign keys.
        foreach ($directory['json'] ?? [] as [$table, $column, $path]) {
            foreach (DB::table($table)->select('id', $column)->whereNotNull($column)->lazyById() as $row) {
                $values = data_get(json_decode($row->$column, true), $path);
                foreach (is_array($values) ? $values : [$values] as $value) {
                    if ($value !== null && (string) $value === (string) $id) return true;
                }
            }
        }
        return false;
    }
}
