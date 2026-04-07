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
        Schema::create('edds_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents', 'id')->cascadeOnDelete();
            $table->integer('condition')->default(1);
            $table->string('type')->nullable();
            $table->string('company')->nullable();
            $table->string('instruction')->nullable();
            $table->string('message')->nullable();
            $table->string('additional_info')->nullable();
            $table->string('elimination_datetime')->nullable();
            $table->boolean('is_consultation')->default(false);
            $table->json('results')->nullable();
            $table->json('responses')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edds_reports');
    }
};
