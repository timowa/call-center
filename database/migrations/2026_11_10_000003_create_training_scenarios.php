<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('training_scenarios', function (Blueprint $t) {
   $t->id(); $t->string('kind', 16); $t->string('name'); $t->text('content'); $t->boolean('active')->default(true); $t->timestamps();
  });
  Schema::create('training_cursors', function (Blueprint $t) {
   $t->id(); $t->foreignId('user_id')->constrained(); $t->string('kind',16); $t->unsignedBigInteger('last_scenario_id')->default(0); $t->unique(['user_id','kind']);
  });
  Schema::create('training_sessions', function (Blueprint $t) {
   $t->id(); $t->foreignId('user_id')->constrained(); $t->foreignId('scenario_id')->constrained('training_scenarios');
   $t->foreignId('incident_id')->nullable()->unique()->constrained(); $t->uuid('request_key');
   $t->string('kind',16); $t->string('name'); $t->text('content'); $t->json('messages'); $t->unsignedInteger('position')->default(0);
   $t->boolean('hung_up')->default(false); $t->timestamps(); $t->unique(['user_id','request_key']);
  });
 }
 public function down(): void { Schema::dropIfExists('training_sessions'); Schema::dropIfExists('training_cursors'); Schema::dropIfExists('training_scenarios'); }
};
