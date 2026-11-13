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
            'ukio.create', 'ukio.view', 'ukio.edit', 'ukio.edit-description',
            '01.create', '01.view', '01.edit',
            'edds.create', 'edds.view', 'edds.edit', 'gas.create', 'gas.view', 'gas.edit'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $cov = Role::firstOrCreate(['name' => 'cov_112']);
        $cov->givePermissionTo([
            'ukio.create', 'ukio.view', 'ukio.edit', 'ukio.edit-description',
            '01.create', '01.view',
            'edds.create', 'edds.view',
        ]);

        $cov->givePermissionTo(['gas.create', 'gas.view']);
        Role::firstOrCreate(['name' => 'gas'])->givePermissionTo(['ukio.create', 'ukio.view', 'ukio.edit-description', 'gas.create', 'gas.view', 'gas.edit']);

        $op01 = Role::firstOrCreate(['name' => 'op_01']);
        $op01->givePermissionTo([
            'ukio.create', 'ukio.view', 'ukio.edit-description',
            '01.create', '01.view', '01.edit',
        ]);

        $edds = Role::firstOrCreate(['name' => 'edds']);
        $edds->givePermissionTo([
            'ukio.create', 'ukio.view', 'ukio.edit-description',
            'edds.create', 'edds.view', 'edds.edit'
        ]);
    }
}
