<?php

namespace Tests\Feature;

use App\{CallType, Condition, Service};
use App\Models\{Incident, TrainingSession, User};
use Database\Seeders\{RolesAndPermissionsSeeder, TrainingScenarioSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerChangesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([RolesAndPermissionsSeeder::class, TrainingScenarioSeeder::class]);
    }

    private function operator(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function createCard(array $extra = []): Incident
    {
        $this->post(route('incidents.store'), array_merge([
            'incoming_number' => '7000', 'call_type_id' => 4, 'services' => [1, 4, 6],
            'description' => 'Исходное описание',
        ], $extra))->assertRedirect()->assertSessionHasNoErrors();
        return Incident::latest('id')->firstOrFail();
    }

    public function test_first_profile_view_updates_only_own_report_and_never_rolls_back(): void
    {
        $this->actingAs($this->operator('cov_112'));
        $card = $this->createCard();
        $this->assertSame(Condition::CONNECTING, $card->condition);
        $this->get(route('incidents.edit', $card))->assertOk();
        $this->assertSame(Condition::CONNECTING, $card->fresh()->condition);
        foreach (['op_01' => 'fireReport', 'edds' => 'eddsReport', 'gas' => 'gasReport'] as $role => $relation) {
            $this->actingAs($this->operator($role));
            $this->assertSame(Condition::CONNECTING, $card->fresh()->$relation->condition);
            $this->get(route('incidents.edit', $card))->assertOk();
            $this->assertSame(Condition::WATCHING, $card->fresh()->condition);
            $this->assertSame(Condition::WATCHING, $card->fresh()->$relation->condition);
            $this->get(route('incidents.edit', $card))->assertOk();
            $this->assertSame(Condition::WATCHING, $card->fresh()->condition);
        }
        $card->condition = Condition::AT_WORK;
        $card->save();
        $this->get(route('incidents.edit', $card))->assertOk();
        $this->assertSame(Condition::AT_WORK, $card->fresh()->condition);
    }

    public function test_completion_works_without_dates_and_requires_all_services(): void
    {
        $this->actingAs($this->operator('cov_112'));
        $card = $this->createCard();
        $this->put(route('incidents.update', $card), ['id' => $card->id, 'call_type_id' => 4, 'complete_service' => '01'])->assertForbidden();
        foreach (['op_01' => '01', 'edds' => 'edds', 'gas' => 'gas'] as $role => $service) {
            $this->actingAs($this->operator($role));
            $payload = ['id' => $card->id, 'call_type_id' => 4, 'complete_service' => $service];
            $this->put(route('incidents.update', $card), $payload)->assertSessionHasNoErrors();
            $this->put(route('incidents.update', $card), $payload)->assertSessionHasNoErrors();
            $this->assertSame($role === 'gas' ? Condition::DONE : Condition::CONNECTING, $card->fresh()->condition);
        }
        $this->assertNull($card->fresh()->gasReport->departed_at);
    }

    public function test_gas_round_trip_permissions_and_old_edds_are_preserved(): void
    {
        $this->assertSame(Service::GAS, CallType::GAS->relatedService());
        $cov = $this->operator('cov_112');
        $this->actingAs($cov);
        $card = $this->createCard(['call_type_id' => 7]);
        $old = $card->eddsReport->getAttributes();
        $fields = ['message' => 'Учебное сообщение', 'utility_id' => DB::table('gas_utilities')->value('id'),
            'event_id' => DB::table('gas_events')->value('id'), 'instruction' => 'Инструкция',
            'departed_at' => '2026-11-10 23:59:00', 'arrived_at' => '2020-01-01 00:00:00', 'measures' => 'Меры'];
        $this->actingAs($this->operator('gas'));
        $this->put(route('incidents.update', $card), ['id' => $card->id, 'call_type_id' => 7, 'gasReport' => ['id' => $card->gasReport->id] + $fields])->assertSessionHasNoErrors();
        $this->assertSame(Condition::CONNECTING, $card->fresh()->gasReport->condition);
        foreach ($fields as $key => $value) $this->assertEquals($value, $card->fresh()->gasReport->$key);
        $this->assertSame($old, $card->fresh()->eddsReport->getAttributes());
        $this->get(route('incidents.edit', $card))->assertInertia(fn (Assert $p) => $p->where('incident.gas_report.message', $fields['message']));
        $this->actingAs($cov);
        $this->put(route('incidents.update', $card), ['id' => $card->id, 'call_type_id' => 7, 'gasReport' => ['message' => 'Запрещено']])->assertForbidden();
        $other = $this->createCard();
        $this->actingAs($this->operator('gas'));
        $this->put(route('incidents.update', $card), ['id' => $card->id, 'call_type_id' => 7, 'gasReport' => ['id' => $other->gasReport->id]])->assertSessionHasErrors('gasReport.id');
    }

    public function test_immediate_department_transition_is_narrow_atomic_and_conflict_checked(): void
    {
        $this->actingAs($this->operator('op_01'));
        DB::table('firefighters_departments')->insert(['id' => 1, 'name' => 'ПЧ-41']);
        $card = $this->createCard(['services' => [1], 'fireReport' => ['departments' => [['department_id' => 1, 'condition_id' => 1]]]]);
        $payload = ['report_id' => $card->fireReport->id, 'department_id' => 1, 'expected_condition' => 1, 'condition_id' => 2,
            'description' => 'Не сохранять', 'fireReport' => ['localized_at' => '2026-01-01 00:00:00']];
        $route = route('incidents.department-transition', $card);
        $this->postJson($route, $payload)->assertOk()->assertJsonPath('row.condition_id', 2);
        $this->assertSame('Исходное описание', $card->fresh()->description);
        $this->assertNull($card->fresh()->fireReport->localized_at);
        $this->postJson($route, $payload)->assertConflict();
        $this->get(route('incidents.dashboard'))->assertOk(); // Cancel/navigation does not undo the transition.
        $this->assertDatabaseHas('department_fire_report', ['department_id' => 1, 'condition_id' => 2]);
        $this->postJson($route, array_merge($payload, ['expected_condition' => 2, 'condition_id' => 4]))->assertUnprocessable();
        $this->postJson($route, array_merge($payload, ['report_id' => 99999]))->assertNotFound();
        $this->actingAs($this->operator('cov_112'));
        $this->postJson($route, $payload)->assertForbidden();
        $outsider = $this->operator('op_01');
        $outsider->area_id = DB::table('areas')->insertGetId(['name' => 'Чужая территория']); $outsider->save();
        $this->actingAs($outsider);
        $this->postJson($route, $payload)->assertForbidden();
    }

    public function test_all_five_filters_and_literal_wildcards_preserve_access_scope(): void
    {
        $this->actingAs($this->operator('cov_112'));
        $type = DB::table('incident_types')->insertGetId(['name' => 'Учебный тип']);
        $card = $this->createCard(['street' => 'Мира', 'incident_type_id' => $type, 'applicant_info' => ['street' => 'Ленина'], 'description' => 'Текст 100%_готов!']);
        $this->createCard(['street' => 'Другая', 'description' => 'Текст 100xxготов!']);
        $filters = ['incident_number' => $card->id, 'filter_incident_type' => $type, 'incident_street' => 'Мира', 'applicant_street' => 'Ленина', 'comment' => '%_'];
        foreach ($filters as $key => $value) {
            $this->get(route('incidents.dashboard', [$key => $value]))->assertInertia(fn (Assert $p) => $p->has('incidents', 1)->where('incidents.0.id', $card->id));
        }
        $this->get(route('incidents.dashboard', $filters))->assertInertia(fn (Assert $p) => $p->has('incidents', 1));
        $this->get(route('incidents.dashboard', array_merge($filters, ['comment' => 'Нет совпадений'])))->assertInertia(fn (Assert $p) => $p->has('incidents', 0));
        $outsider = $this->operator('gas');
        $outsider->area_id = DB::table('areas')->insertGetId(['name' => 'Другой район']); $outsider->save();
        $this->actingAs($outsider);
        $this->get(route('incidents.dashboard', $filters))->assertInertia(fn (Assert $p) => $p->has('incidents', 0)->has('filterStreets.incident', 0)->has('filterStreets.applicant', 0));
    }

    public function test_training_rotation_retries_independence_and_reopening(): void
    {
        $user = $this->operator('cov_112'); $this->actingAs($user);
        foreach (['call' => 2, 'sms' => 3, 'glonass' => 4] as $kind => $source) {
            $ids = [];
            for ($i = 0; $i < 3; $i++) {
                $key = (string) Str::uuid();
                $this->post(route('training.accept'), ['kind' => $kind, 'request_key' => $key])->assertRedirect();
                $session = TrainingSession::latest('id')->firstOrFail(); $ids[] = $session->scenario_id;
                $this->post(route('training.accept'), ['kind' => $kind, 'request_key' => $key])->assertRedirect(route('training.show', $session));
                $this->get(route('training.show', $session))->assertOk();
                $this->get(route('training.show', $session))->assertOk();
                $this->assertSame($session->id, TrainingSession::latest('id')->value('id'));
            }
            $this->assertSame($ids[0], $ids[2]); $this->assertNotSame($ids[0], $ids[1]);
            $card = $this->createCard(['source_id' => $source, 'training_session_id' => $session->id]);
            $count = Incident::count();
            $this->post(route('incidents.store'), ['call_type_id' => 4, 'incoming_number' => '7000', 'training_session_id' => $session->id])->assertRedirect();
            $this->assertSame($count, Incident::count());
            $this->get(route('incidents.edit', $card))->assertInertia(fn (Assert $p) => $p->where('incident.training_session.id', $session->id)->where('incident.training_session.content', $session->content)->where('incident.source_id', $source));
            $other = $this->operator('cov_112'); $this->actingAs($other);
            $this->post(route('training.accept'), ['kind' => $kind, 'request_key' => (string) Str::uuid()])->assertRedirect();
            $this->assertSame($ids[0], TrainingSession::latest('id')->value('scenario_id'));
            $this->actingAs($user);
        }
    }

    public function test_sms_order_deduplication_exhaustion_and_foreign_session(): void
    {
        $this->actingAs($this->operator('cov_112'));
        $this->post(route('training.accept'), ['kind' => 'sms', 'request_key' => (string) Str::uuid()]);
        $session = TrainingSession::latest('id')->firstOrFail();
        $this->assertCount(1, $session->messages);
        for ($i = 0; $i < 4; $i++) {
            $payload = ['text' => 'Ответ '.$i, 'request_key' => (string) Str::uuid()];
            $this->postJson(route('training.reply', $session), $payload)->assertOk();
            $before = $session->fresh()->messages;
            $this->postJson(route('training.reply', $session), $payload)->assertOk();
            $this->assertSame($before, $session->fresh()->messages);
        }
        $this->assertSame(3, $session->fresh()->position);
        $this->assertSame(['incoming', 'outgoing', 'incoming', 'outgoing', 'incoming', 'outgoing', 'outgoing'], array_column($session->fresh()->messages, 'direction'));
        $this->actingAs($this->operator('cov_112'));
        $this->get(route('training.show', $session))->assertForbidden();
        $this->postJson(route('training.reply', $session), ['text' => 'Чужой', 'request_key' => (string) Str::uuid()])->assertForbidden();
    }

    public function test_legacy_status_migration_is_data_preserving_and_not_reversed(): void
    {
        $this->actingAs($this->operator('cov_112'));
        $card = $this->createCard();
        foreach (['incidents' => $card->id, 'fire_reports' => $card->fireReport->id, 'edds_reports' => $card->eddsReport->id] as $table => $id) DB::table($table)->where('id', $id)->update(['condition' => 1]);
        $migration = require database_path('migrations/2026_11_10_000001_retire_request_condition.php');
        $migration->up(); $migration->down();
        $this->assertSame(Condition::CONNECTING, $card->fresh()->condition);
        $this->assertSame(Condition::CONNECTING, $card->fresh()->fireReport->condition);
        $this->assertSame(Condition::CONNECTING, $card->fresh()->eddsReport->condition);
        $this->assertSame('Исходное описание', $card->fresh()->description);
        $this->get(route('incidents.dashboard', ['conditions' => [1]]))->assertInertia(fn (Assert $p) => $p->has('incidents', 1)->missing('dictionaries.conditions.1'));
    }

    public function test_cov_save_rejects_dom_event_action_and_preserves_neighbor_reports(): void
    {
        $this->actingAs($this->operator('cov_112'));
        $this->post(route('incidents.store'), ['call_type_id' => 4, 'incoming_number' => '7000', 'actionType' => ['isTrusted' => true]])->assertSessionHasErrors('actionType');
        $card = $this->createCard(['actionType' => 'transfer']);
        $before = $card->fireReport->getAttributes();
        $this->put(route('incidents.update', $card), ['id' => $card->id, 'call_type_id' => 4, 'description' => 'Исправлено', 'fireReport' => $before])->assertSessionHasNoErrors();
        $this->assertSame('Исправлено', $card->fresh()->description);
        $this->assertSame($before, $card->fresh()->fireReport->getAttributes());
        $this->post(route('incidents.store'), ['call_type_id' => 3, 'incoming_number' => '7000', 'actionType' => 'transfer'])->assertSessionHasErrors('actionType');
        $this->post(route('incidents.store'), ['call_type_id' => 1, 'incoming_number' => '7000', 'source_id' => 2, 'actionType' => 'false'])->assertSessionHasNoErrors();
        $this->assertSame(Condition::DONE, Incident::latest('id')->first()->condition);
    }
}
