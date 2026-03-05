<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            'incidents.create',
            'incidents.view-all-districts',
            'incidents.view-own-district',
            'incidents.edit.all-fields',
            'incidents.edit.description-only',
            'incidents.edit.any-district',
            'tabs.01.edit', 'tabs.01.view',
            'tabs.04.edit', 'tabs.04.view',
            'tabs.jkh.edit', 'tabs.jkh.view',
            'tabs.all.view'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $cov = Role::firstOrCreate(['name' => 'cov_112']);
        $cov->givePermissionTo([
            'incidents.create', 'incidents.view-all-districts',
            'incidents.edit.all-fields', 'incidents.edit.any-district',
            'tabs.all.view'
        ]);

        $op01 = Role::firstOrCreate(['name' => 'op_01']);
        $op01->givePermissionTo([
            'incidents.create', 'incidents.view-own-district',
            'incidents.edit.description-only',
            'tabs.01.edit', 'tabs.01.view'
        ]);

        $edds = Role::firstOrCreate(['name' => 'edds']);
        $edds->givePermissionTo([
            'incidents.create', 'incidents.view-own-district',
            'incidents.edit.all-fields',
            'tabs.all.view', 'tabs.jkh.edit'
        ]);
    }
}
