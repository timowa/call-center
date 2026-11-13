<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\{Permission, Role};
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $grants = [
            'cov_112' => ['01.edit', 'edds.edit', 'gas.edit'],
            'edds' => ['ukio.edit', '01.view', '01.edit', 'gas.view', 'gas.edit'],
        ];
        foreach ($grants as $role => $names) {
            $permissions = array_map(fn ($name) => Permission::findOrCreate($name, 'web'), $names);
            Role::findOrCreate($role, 'web')->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        // Preserve permissions that may have been granted independently.
    }
};
