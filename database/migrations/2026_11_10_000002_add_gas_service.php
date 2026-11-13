<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\{Permission, Role};
use Spatie\Permission\PermissionRegistrar;
return new class extends Migration {
    public function up(): void {
        foreach (['gas_utilities', 'gas_events'] as $table) Schema::create($table, function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('gas_reports', function (Blueprint $t) {
            $t->id(); $t->foreignId('incident_id')->unique()->constrained();
            $t->integer('condition')->default(2);
            $t->foreignId('utility_id')->nullable()->constrained('gas_utilities');
            $t->foreignId('event_id')->nullable()->constrained('gas_events');
            foreach (['message', 'instruction', 'measures'] as $field) $t->text($field)->nullable();
            $t->dateTime('departed_at')->nullable(); $t->dateTime('arrived_at')->nullable(); $t->timestamps();
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['gas.create','gas.view','gas.edit','edds.edit','ukio.create','ukio.view','ukio.edit-description'] as $name) Permission::findOrCreate($name, 'web');
        Role::findOrCreate('gas', 'web')->givePermissionTo(['gas.create','gas.view','gas.edit','ukio.create','ukio.view','ukio.edit-description']);
        Role::where('name','cov_112')->first()?->givePermissionTo(['gas.create','gas.view']);
        Role::where('name','edds')->first()?->givePermissionTo('edds.edit');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void {
        Schema::dropIfExists('gas_reports'); Schema::dropIfExists('gas_events'); Schema::dropIfExists('gas_utilities');
        // Permissions are additive; do not revoke independently assigned permissions.
    }
};
