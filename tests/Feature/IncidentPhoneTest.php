<?php

namespace Tests\Feature;

use App\Models\{Incident, User};
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncidentPhoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function operator(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function incident(): Incident
    {
        $this->actingAs($this->operator('cov_112'));
        $this->post(route('incidents.store'), [
            'call_type_id' => 4, 'incoming_number' => '7000', 'services' => [1, 4],
            'fireReport' => [], 'eddsReport' => [],
            'applicant_info' => ['lastname' => 'Иванов', 'street' => 'Мира', 'phone' => '7000'],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $incident = Incident::latest('id')->firstOrFail();
        // Both reports are needed for multi-role access under the existing policy.
        if (!$incident->eddsReport) $incident->eddsReport()->create([]);
        return $incident->fresh();
    }

    private function savePhone(Incident $incident, mixed $info)
    {
        return $this->put(route('incidents.update', $incident->id), [
            'id' => $incident->id, 'call_type_id' => 4, 'applicant_info' => $info,
        ]);
    }

    public function test_roles_save_phone_and_reopen_without_changing_reports_or_applicant(): void
    {
        $incident = $this->incident();
        foreach (['cov_112', 'op_01', 'edds'] as $role) {
            $this->actingAs($this->operator($role));
            $before = $incident->fresh();
            $phone = '7123'.$role;
            $this->savePhone($incident, array_merge($before->applicant_info, ['phone' => $phone]))
                ->assertRedirect()->assertSessionHasNoErrors();
            $fresh = $incident->fresh();
            $this->assertSame(['lastname' => 'Иванов', 'street' => 'Мира', 'phone' => $phone], $fresh->applicant_info);
            $this->assertSame($before->condition, $fresh->condition);
            $this->assertSame($before->fireReport->getAttributes(), $fresh->fireReport->getAttributes());
            $this->assertSame($before->eddsReport->getAttributes(), $fresh->eddsReport->getAttributes());
            $this->get(route('incidents.edit', $incident->id))->assertInertia(fn (Assert $page) => $page
                ->where('incident.applicant_info.phone', $phone)->where('canUpdatePhone', true));
            $this->get(route('incidents.view', $incident->id))->assertInertia(fn (Assert $page) => $page
                ->where('mode', 'view')->where('canUpdatePhone', false));
            $this->get(route('incidents.dashboard'))->assertInertia(fn (Assert $page) => $page->has('incidents', 1));
        }
    }

    public function test_narrow_update_preserves_omitted_fields_and_rejects_protected_changes(): void
    {
        $incident = $this->incident();
        foreach (['op_01', 'edds'] as $role) {
            $this->actingAs($this->operator($role));
            $this->savePhone($incident, ['phone' => '7999'])->assertSessionHasNoErrors();
            $this->assertSame('Иванов', $incident->fresh()->applicant_info['lastname']);
            foreach ([['phone' => '7888', 'lastname' => 'Подмена'], ['phone' => '7888', 'street' => 'Подмена'], null] as $info) {
                $this->savePhone($incident, $info)->assertForbidden();
                $this->assertSame('7999', $incident->fresh()->applicant_info['phone']);
            }
            $this->savePhone($incident, ['phone' => ['invalid']])->assertSessionHasErrors('applicant_info.phone');
            $this->savePhone($incident, ['phone' => ''])->assertSessionHasNoErrors();
            $this->assertNull($incident->fresh()->applicant_info['phone']);
        }
    }

    public function test_foreign_area_missing_service_and_view_only_user_cannot_update_phone(): void
    {
        $incident = $this->incident();
        foreach (['op_01', 'edds'] as $role) {
            $user = $this->operator($role);
            $user->area_id = DB::table('areas')->insertGetId(['name' => 'Чужой район']);
            $user->save();
            $this->actingAs($user);
            $this->savePhone($incident, ['phone' => '7999'])->assertForbidden();
        }
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('ukio.view');
        $this->actingAs($viewer);
        $this->savePhone($incident, ['phone' => '7999'])->assertForbidden();
        $this->actingAs($this->operator('op_01'));
        $incident->fireReport()->delete();
        $this->savePhone($incident, ['phone' => '7999'])->assertForbidden();
        $this->assertSame('7000', $incident->fresh()->applicant_info['phone']);
    }
}
