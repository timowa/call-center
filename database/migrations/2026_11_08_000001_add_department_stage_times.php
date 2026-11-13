<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $duplicates = DB::table('department_fire_report')
            ->select('fire_report_id', 'department_id')->selectRaw('COUNT(*) AS total')
            ->groupBy('fire_report_id', 'department_id')->havingRaw('COUNT(*) > 1')->get();
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Дубли отделений требуют ручного решения: '.$duplicates->toJson());
        }
        Schema::table('department_fire_report', function (Blueprint $table) {
            foreach (['requested_at', 'accepted_at', 'arrived_at', 'completed_at'] as $field) {
                $table->dateTime($field)->nullable();
            }
            $table->unique(['fire_report_id', 'department_id'], 'report_department_unique');
        });
    }

    public function down(): void
    {
        Schema::table('department_fire_report', function (Blueprint $table) {
            $table->dropUnique('report_department_unique');
            $table->dropColumn(['requested_at', 'accepted_at', 'arrived_at', 'completed_at']);
        });
    }
};
