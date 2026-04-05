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
        Schema::create('fire_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->unique()->constrained('incidents');
            $table->integer('condition')->default(1);
            $table->foreignId('report_type_id')->nullable()->constrained('fire_report_types');
            $table->boolean('is_error_card')->default(false);
            $table->integer('type_of_fire_protection')->nullable();
            $table->foreignId('object_id')->nullable()->constrained('urban_objects');
            $table->foreignId('object_type_id')->nullable()->constrained('urban_object_types');
            $table->integer('plan')->nullable();
            $table->integer('rank')->nullable();
            $table->integer('card_number')->nullable();
            $table->integer('shift')->nullable();
            $table->integer('water_consumption')->nullable();
            $table->integer('first_water_barrel_minutes')->nullable();
            $table->integer('water_source')->nullable();
            $table->integer('dead_total')->nullable();
            $table->integer('dead_children')->nullable();
            $table->integer('dead_staff')->nullable();
            $table->integer('injured_total')->nullable();
            $table->integer('injured_children')->nullable();
            $table->integer('injured_staff')->nullable();
            $table->integer('rescued_children')->nullable();
            $table->integer('rescued_staff')->nullable();
            $table->integer('violated_children')->nullable();
            $table->integer('violated_staff')->nullable();
            $table->json('info')->nullable();
            $table->json('barrels')->nullable();
            $table->json('fire_extinguishing_agents')->nullable();
            $table->json('rtp')->nullable();
            $table->json('personnel')->nullable();
            $table->json('gdzs')->nullable();
            $table->foreignId('cause_id')->nullable()->constrained('causes_of_the_fire');
            $table->timestamp('localized_at')->nullable();
            $table->timestamp('fire_eliminated_at')->nullable();
            $table->timestamp('elimination_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fire_reports');
    }
};
