<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('department_fire_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('firefighters_departments')->references('id');
            $table->foreignId('fire_report_id')->constrained('fire_reports')->references('id');
            $table->foreignId('user_id')->constrained('users')->references('id');
            $table->integer('condition_id')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_fire_report');
    }
};
