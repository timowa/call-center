<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        foreach (['incidents', 'fire_reports', 'edds_reports'] as $table) {
            DB::table($table)->where('condition', 1)->update(['condition' => 2]);
            $column = collect(Schema::getColumns($table))->firstWhere('name', 'condition');
            if (trim((string) $column['default'], "'\"") !== '2') {
                Schema::withoutForeignKeyConstraints(fn () => Schema::table($table, fn (Blueprint $t) => $t->integer('condition')->default(2)->change()));
            }
        }
        // department_fire_report.condition_id uses a different enum (Заявка создана).
    }

    public function down(): void
    {
        // Preserve data: old and new CONNECTING rows are indistinguishable.
    }
};
