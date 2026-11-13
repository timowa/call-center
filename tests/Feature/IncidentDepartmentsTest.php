<?php

namespace Tests\Feature;

use App\Condition;
use App\Models\{Incident, User};
use App\Services\DepartmentStages;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncidentDepartmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actingAs($this->operator());
        DB::table('firefighters_departments')->insert([['id' => 1, 'name' => 'ПЧ-41'], ['id' => 2, 'name' => 'ПЧ-42']]);
        $this->travelTo(now()->setDate(2026, 11, 8)->setTime(23, 59, 47));
    }

    private function operator(string $role = 'op_01', array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    private function row(int $id = 1, int $state = 1): array
    {
        return ['department_id' => $id, 'condition_id' => $state, 'requested_at' => '2026-11-08 23:59:47', 'accepted_at' => null, 'arrived_at' => null, 'completed_at' => null, 'transitions' => []];
    }

    private function createIncident(array $rows = [], array $report = []): Incident
    {
        $this->post(route('incidents.store'), [
            'call_type_id' => 4, 'incoming_number' => '70000000000', 'services' => [1],
            'fireReport' => array_merge(['departments' => $rows], $report),
        ])->assertRedirect()->assertSessionHasNoErrors();
        return Incident::latest('id')->firstOrFail();
    }

    private function save(Incident $incident, array $rows, array $report = [], array $extra = [])
    {
        return $this->put(route('incidents.update', $incident->id), array_merge([
            'id' => $incident->id, 'call_type_id' => 4,
            'fireReport' => array_merge(['id' => $incident->fireReport->id, 'departments' => $rows], $report),
        ], $extra));
    }

    public function test_creation_round_trip_and_sequential_transitions_reach_list_and_filter(): void
    {
        $row = $this->row();
        $incident = $this->createIncident([$row]);
        $this->get(route('incidents.edit', $incident->id))->assertInertia(fn (Assert $page) => $page
            ->component('Incidents/Edit')->where('incident.fire_report.departments.0.requested_at', $row['requested_at'])
            ->where('incident.fire_report.departments.0.operator_name', auth()->user()->name.' ('.auth()->user()->uid.')'));
        foreach ([1 => Condition::RESPONSE, 2 => Condition::RESPONSE, 3 => Condition::AT_WORK, 4 => Condition::DONE] as $step => $condition) {
            if ($step > 1) {
                $row['condition_id'] = $step;
                $row['transitions'] = [$step];
                $row[DepartmentStages::FIELDS[$step]] = now()->format('Y-m-d H:i:s');
                $this->save($incident, [$row])->assertSessionHasNoErrors();
            }
            $this->assertSame($condition, $incident->fresh()->condition);
            $this->assertSame($condition, $incident->fireReport->fresh()->condition);
            $this->get(route('incidents.dashboard', ['conditions' => [$condition->value]]))->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')->has('incidents', 1)->where('incidents.0.condition', $condition->value));
        }
    }

    public function test_multiple_draft_transitions_and_arbitrary_chronology_survive(): void
    {
        $row = array_merge($this->row(), ['condition_id' => 4, 'transitions' => [2, 3, 4],
            'accepted_at' => '2020-01-01 00:00:00', 'arrived_at' => '2020-01-01 00:00:00', 'completed_at' => '2030-12-31 23:59:59']);
        $incident = $this->createIncident([$row, $this->row(2)]);
        $this->assertCount(2, $incident->fireReport->departments);
        // Preserve the existing max-state aggregation as requested.
        $this->assertSame(Condition::DONE, $incident->fireReport->condition);
        $row['transitions'] = [];
        foreach (DepartmentStages::FIELDS as $field) {
            $row[$field] = '2021-02-03 04:05:06';
            $this->save($incident, [$row])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('department_fire_report', ['department_id' => 1, $field => $row[$field], 'condition_id' => 4]);
        }
        $this->assertCount(2, $incident->fireReport->fresh()->departments);
    }

    public function test_unchanged_save_preserves_author_and_stage_times(): void
    {
        $incident = $this->createIncident([$this->row()]);
        $before = DB::table('department_fire_report')->first();
        $this->actingAs($this->operator());
        $this->travel(1)->hours();
        $this->save($incident, [$this->row()])->assertSessionHasNoErrors();
        $this->assertEquals($before, DB::table('department_fire_report')->first());
    }

    public function test_invalid_dates_ids_duplicates_and_skipped_transitions_are_atomic(): void
    {
        $incident = $this->createIncident([$this->row()]);
        $before = DB::table('department_fire_report')->first();
        foreach ([
            [array_merge($this->row(), ['accepted_at' => '2026-02-30 25:00:00'])],
            [$this->row(), $this->row()],
            [$this->row(999)],
            [array_merge($this->row(), ['condition_id' => 3, 'arrived_at' => '2026-11-09 00:00:00'])],
            [array_merge($this->row(), ['requested_at' => null])],
            [array_merge($this->row(), ['accepted_at' => '2026-11-09 00:00:00'])],
        ] as $rows) {
            $this->save($incident, $rows, [], ['description' => 'Не должно сохраниться'])->assertSessionHasErrors();
            $this->assertEquals($before, DB::table('department_fire_report')->first());
            $this->assertNull($incident->fresh()->description);
        }
        $other = $this->createIncident();
        $this->save($incident, [$this->row()], ['id' => $other->fireReport->id])->assertSessionHasErrors('fireReport.id');
    }

    public function test_other_roles_cannot_change_01_but_unchanged_report_is_preserved(): void
    {
        $incident = $this->createIncident([$this->row()]);
        $incident->eddsReport()->create(['message' => 'Сохранить ЕДДС']);
        foreach (['cov_112', 'edds'] as $role) {
            $this->actingAs($this->operator($role));
            $this->save($incident, [array_merge($this->row(), ['requested_at' => '2020-01-01 00:00:00'])])->assertForbidden();
            $this->save($incident, [$this->row()], [], ['description' => $role])->assertSessionHasNoErrors();
            $this->assertSame('Сохранить ЕДДС', $incident->eddsReport->fresh()->message);
        }
    }

    public function test_foreign_territory_and_ukio_changes_are_denied(): void
    {
        $incident = $this->createIncident([$this->row()]);
        $area = DB::table('areas')->insertGetId(['name' => 'Другая территория']);
        $this->actingAs($this->operator('op_01', ['area_id' => $area]));
        $this->save($incident, [$this->row()])->assertForbidden();
        $this->get(route('incidents.edit', $incident->id))->assertForbidden();
        $this->get(route('incidents.view', $incident->id))->assertForbidden();
        $this->actingAs($this->operator());
        $this->save($incident, [$this->row()], [], ['street' => 'Подмена'])->assertForbidden();
    }

    public function test_legacy_unknown_times_are_not_invented_and_no_service_is_created_on_update(): void
    {
        $incident = $this->createIncident();
        $incident->fireReport->departments()->attach(1, ['condition_id' => 3, 'user_id' => auth()->id()]);
        $row = array_merge($this->row(1, 3), ['requested_at' => null]);
        $this->save($incident, [$row])->assertSessionHasNoErrors();
        foreach (DepartmentStages::FIELDS as $field) $this->assertNull(DB::table('department_fire_report')->value($field));
        $this->actingAs($this->operator('cov_112'));
        $this->post(route('incidents.store'), ['call_type_id' => 1, 'incoming_number' => '7000', 'services' => []])->assertSessionHasNoErrors();
        $plain = Incident::latest('id')->first();
        $this->put(route('incidents.update', $plain->id), ['id' => $plain->id, 'call_type_id' => 1, 'fireReport' => ['id' => null, 'departments' => []]])->assertSessionHasNoErrors();
        $this->assertNull($plain->fresh()->fireReport);
    }

    public function test_all_response_and_supply_dates_round_trip_and_clear(): void
    {
        $date = '2026-11-09 00:00:07';
        $report = ['localized_at' => $date, 'fire_eliminated_at' => $date, 'elimination_at' => $date,
            'barrels' => [['type' => 1, 'time' => $date]], 'fire_extinguishing_agents' => [['time' => $date]]];
        $incident = $this->createIncident([], $report);
        $this->get(route('incidents.edit', $incident->id))->assertInertia(function (Assert $page) use ($date) {
            $page->component('Incidents/Edit');
            foreach (['localized_at', 'fire_eliminated_at', 'elimination_at', 'barrels.0.time', 'fire_extinguishing_agents.0.time'] as $path) $page->where('incident.fire_report.'.$path, $date);
        });
        $this->save($incident, [], ['barrels' => [['time' => null]], 'fire_extinguishing_agents' => [['time' => null]]])->assertSessionHasNoErrors();
        $this->assertNull($incident->fireReport->fresh()->barrels[0]['time']);
        $this->assertNull($incident->fireReport->fresh()->fire_extinguishing_agents[0]['time']);
    }

    public function test_missing_creation_time_uses_clock_and_unauthorized_creation_rolls_back(): void
    {
        $row = $this->row();
        unset($row['requested_at']);
        $this->createIncident([$row]);
        $this->assertDatabaseHas('department_fire_report', ['requested_at' => now()->format('Y-m-d H:i:s')]);
        $this->actingAs($this->operator('cov_112'));
        $count = Incident::count();
        $this->post(route('incidents.store'), ['call_type_id' => 4, 'incoming_number' => '7000', 'services' => [1], 'fireReport' => ['departments' => [$this->row()]]])->assertForbidden();
        $this->assertSame($count, Incident::count());
    }

    public function test_multiple_transitions_in_existing_draft_and_legacy_json_dates(): void
    {
        $incident = $this->createIncident([$this->row()], ['barrels' => [['time' => '2026-11-08T12:34']]]);
        $this->assertSame('2026-11-08 12:34:00', $incident->fireReport->barrels[0]['time']);
        $row = array_merge($this->row(), ['condition_id' => 4, 'transitions' => [2,3,4],
            'accepted_at' => '2020-01-01 00:00:00', 'arrived_at' => '2020-01-01 00:00:00', 'completed_at' => '2030-01-01 00:00:00']);
        $this->save($incident, [$row])->assertSessionHasNoErrors();
        $this->assertSame(Condition::DONE, $incident->fresh()->condition);
        $row['transitions'] = [];
        $this->save($incident, [$row])->assertSessionHasNoErrors();
        $this->assertSame(Condition::DONE, $incident->fresh()->condition);
    }

    public function test_time_only_changes_do_not_recompute_mixed_service_states(): void
    {
        $incident = $this->createIncident([$this->row()]);
        $incident->eddsReport()->create(['message' => 'Не менять']);
        $incident->condition = Condition::CONNECTING;
        $incident->save();
        $this->save($incident, [array_merge($this->row(), ['requested_at' => '2020-01-01 00:00:00'])])->assertSessionHasNoErrors();
        $this->assertSame(Condition::CONNECTING, $incident->fresh()->condition);
        $this->assertSame(Condition::CONNECTING, $incident->eddsReport->fresh()->condition);
    }

    public function test_uid_login_starts_new_work_session(): void
    {
        $user = auth()->user();
        $this->post(route('logout'));
        $this->post(route('login'), ['uid' => (string) $user->uid, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $first = session('work_session');
        $this->assertNotEmpty($first);
        $this->post(route('logout'));
        $this->post(route('login'), ['uid' => (string) $user->uid, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertNotSame($first, session('work_session'));
    }
}
