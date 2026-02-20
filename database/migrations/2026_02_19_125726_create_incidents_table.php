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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('incident_type_id')->nullable()->constrained('incident_types');
            $table->foreignId('call_type_id')->nullable()->constrained('call_types');
            $table->boolean('is_training')->default(false);
            $table->boolean('is_important')->default(false);
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->text('street')->nullable();
            $table->integer('house_number')->nullable();
            $table->integer('corpus_number')->nullable();
            $table->integer('apartment_number')->nullable();
            $table->integer('entrance_number')->nullable();
            $table->integer('entrance_code')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('number_of_floors')->nullable();
            $table->string('ownership')->nullable();
            $table->string('building')->nullable();
            $table->string('additional_street')->nullable();
            $table->string('district_of_city')->nullable();
            $table->string('object')->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->string('road')->nullable();
            $table->string('metre')->nullable();
            $table->string('km')->nullable();
            $table->boolean('is_nearby')->default(false);
            $table->longText('address_section')->nullable();
            $table->longText('additional_info')->nullable();
            $table->boolean('emergency_threat')->default(false);
            $table->boolean('threat_to_people')->nullable();
            $table->integer('number_of_victims')->nullable();
            $table->foreignId('emergency_type_id')->nullable()->constrained('emergency_types');
            $table->longText('description')->nullable();
            $table->json('applicant_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
