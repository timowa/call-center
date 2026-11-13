<?php

namespace Tests\Feature;

use App\Models\{Admin, Incident, User};
use App\Support\AdminDirectories;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDirectoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function loginAdmin(): void
    {
        Admin::create(['login' => 'administrator', 'password' => 'test-password-123']);
        $this->post('/admin/login', ['login' => 'administrator', 'password' => 'test-password-123'])->assertRedirect('/admin');
    }

    public function test_admin_seeder_restores_credentials_without_duplicates(): void
    {
        Admin::create(['login' => 'admin', 'password' => 'previous-password']);
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->assertSame(1, Admin::where('login', 'admin')->count());
        $this->assertNotSame('admin', Admin::where('login', 'admin')->value('password'));
        $this->post('/admin/login', ['login' => 'admin', 'password' => 'admin'])->assertRedirect('/admin');
        $this->assertAuthenticated('admin');
        $this->assertGuest('web');
    }

    public function test_every_crud_endpoint_requires_separate_admin_login(): void
    {
        foreach ([null, User::factory()->create()] as $user) {
            if ($user) $this->actingAs($user);
            $this->get('/admin')->assertRedirect('/admin/login');
            foreach (AdminDirectories::all() as $key => $definition) {
                $url = '/admin/directories/'.$key;
                $this->get($url)->assertRedirect('/admin/login');
                $this->post($url, ['name' => 'Запрещено'])->assertRedirect('/admin/login');
                $this->put($url.'/1', ['name' => 'Запрещено'])->assertRedirect('/admin/login');
                $this->delete($url.'/1')->assertRedirect('/admin/login');
            }
        }
    }

    public function test_login_and_logout_do_not_replace_operator_authentication(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->loginAdmin();
        $this->assertAuthenticatedAs($user, 'web');
        $this->get('/admin')->assertOk();
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($user, 'web');
        $this->post('/admin/login', ['login' => 'administrator', 'password' => 'test-password-123']);
        $this->post('/logout');
        $this->assertGuest('web');
        $this->assertAuthenticated('admin');
    }

    public function test_admin_cannot_enter_operator_pages_and_bad_credentials_are_rejected(): void
    {
        $this->post('/admin/login', ['login' => 'unknown', 'password' => 'wrong'])->assertSessionHasErrors('login');
        $this->assertGuest('admin');
        $this->loginAdmin();
        $this->assertGuest('web');
        $this->get('/incidents/create-instant')->assertRedirect('/login');
    }

    public function test_all_twelve_directories_support_crud_and_validate_input(): void
    {
        $this->loginAdmin();
        $area = DB::table('areas')->insertGetId(['name' => 'Район']);
        foreach (AdminDirectories::all() as $key => $definition) {
            $url = '/admin/directories/'.$key;
            $this->get($url)->assertOk();
            $this->post($url, ['name' => '   '])->assertSessionHasErrors('name');
            $data = ['name' => 'Новое '.$key];
            if ($definition['area'] ?? false) {
                $this->post($url, $data)->assertSessionHasErrors('area_id');
                $this->post($url, $data + ['area_id' => 999999])->assertSessionHasErrors('area_id');
                $data['area_id'] = $area;
            }
            $this->post($url, $data)->assertSessionHasNoErrors();
            $id = DB::table($definition['table'])->where('name', $data['name'])->value('id');
            $this->assertNotNull($id);
            $data['name'] = 'Изменено '.$key;
            $this->put($url.'/'.$id, $data)->assertSessionHasNoErrors();
            $this->assertDatabaseHas($definition['table'], ['id' => $id] + $data);
            $this->get($url.'?q=Изменено')->assertInertia(fn (Assert $page) => $page->has('rows.data', 1)->where('rows.data.0.name', $data['name']));
            $this->delete($url.'/'.$id)->assertSessionHasNoErrors();
            $this->assertDatabaseMissing($definition['table'], ['id' => $id]);
        }
        $this->post('/admin/directories/users', ['name' => 'No'])->assertNotFound();
        $this->put('/admin/directories/incident-types/999999', ['name' => 'No'])->assertNotFound();
    }

    public function test_created_values_reach_forms_survive_card_save_and_cannot_be_deleted_when_used(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->loginAdmin();
        $area = DB::table('areas')->insertGetId(['name' => 'Новый район']);
        $ids = [];
        foreach (AdminDirectories::all() as $key => $definition) {
            $data = ['name' => 'Создано в админке '.$key];
            if ($definition['area'] ?? false) $data['area_id'] = $area;
            $this->post('/admin/directories/'.$key, $data)->assertSessionHasNoErrors();
            $ids[$key] = DB::table($definition['table'])->where('name', $data['name'])->value('id');
        }
        $user = User::factory()->create();
        $user->assignRole(['cov_112', 'op_01', 'gas']);
        $this->actingAs($user, 'web');
        $this->get('/incidents/create-instant')->assertInertia(fn (Assert $page) => $page
            ->where('directories.incidentTypes.0.id', $ids['incident-types'])
            ->where('directories.districts.0.id', $ids['districts'])
            ->where('directories.urban_objects.0.id', $ids['objects'])
            ->where('directories.gasUtilities.0.id', $ids['gas-utilities'])
            ->where('directories.gasEvents.0.id', $ids['gas-events']));
        $mapping = ['report-types' => 'report_types', 'objects' => 'objects', 'object-types' => 'object_types', 'water-sources' => 'water_sources', 'fire-causes' => 'causes', 'posts' => 'posts', 'extinguishing-agents' => 'fire_extinguishing_agents', 'departments' => 'departments'];
        $response = $this->getJson('/api/dictionaries/get-fire-directories')->assertOk();
        foreach ($mapping as $key => $property) $response->assertJsonPath($property.'.0.id', $ids[$key]);
        $fire = [
            'report_type_id' => $ids['report-types'], 'object_id' => $ids['objects'], 'object_type_id' => $ids['object-types'],
            'water_source_id' => $ids['water-sources'], 'cause_id' => $ids['fire-causes'],
            'rtp' => [['fio' => 'Иванов', 'post_id' => $ids['posts']]],
            'personnel' => [['fio' => 'Петров', 'post_id' => $ids['posts']]],
            'fire_extinguishing_agents' => [['type' => $ids['extinguishing-agents'], 'count' => 1]],
            'departments' => [['department_id' => $ids['departments'], 'condition_id' => 1]],
        ];
        $gas = ['utility_id' => $ids['gas-utilities'], 'event_id' => $ids['gas-events']];
        $this->post('/incidents/store', [
            'incoming_number' => '7000', 'call_type_id' => 4, 'services' => [1, 6],
            'incident_type_id' => $ids['incident-types'], 'district_id' => $ids['districts'], 'object' => (string) $ids['objects'],
            'applicant_info' => ['area' => $ids['districts']], 'fireReport' => $fire, 'gasReport' => $gas,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $incident = Incident::latest('id')->firstOrFail();
        $this->get('/incidents/edit/'.$incident->id)->assertInertia(fn (Assert $page) => $page
            ->where('incident.incident_type_id', $ids['incident-types'])
            ->where('incident.district_id', $ids['districts'])
            ->where('incident.fire_report.report_type_id', $ids['report-types'])
            ->where('incident.fire_report.departments.0.department_id', $ids['departments'])
            ->where('incident.gas_report.utility_id', $ids['gas-utilities']));
        $this->put('/incidents/update/'.$incident->id, [
            'id' => $incident->id, 'call_type_id' => 4, 'fireReport' => $fire, 'gasReport' => $gas,
        ])->assertSessionHasNoErrors();
        foreach (array_diff_key($fire, ['departments' => true]) as $field => $value) $this->assertEquals($value, $incident->fresh()->fireReport->$field);
        foreach ($gas as $field => $value) $this->assertEquals($value, $incident->fresh()->gasReport->$field);
        foreach (AdminDirectories::all() as $key => $definition) {
            $this->delete('/admin/directories/'.$key.'/'.$ids[$key])->assertSessionHasErrors('delete');
            $this->assertDatabaseHas($definition['table'], ['id' => $ids[$key]]);
        }
        // Check the JSON-only applicant reference, without a district FK.
        $incident->update(['district_id' => null]);
        $this->delete('/admin/directories/districts/'.$ids['districts'])->assertSessionHasErrors('delete');
        $otherArea = DB::table('areas')->insertGetId(['name' => 'Другой район']);
        $this->put('/admin/directories/districts/'.$ids['districts'], ['name' => 'Новое имя', 'area_id' => $otherArea])->assertSessionHasErrors('area_id');
        $this->get('/incidents/dashboard')->assertOk();
    }
}
