<?php
// Creates fixtures only in the explicitly isolated UI-review database.
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || !str_ends_with(config('database.connections.sqlite.database'), '/ui-review.sqlite')) throw new RuntimeException('Isolated UI database required');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
if (App\Models\User::count()) exit("UI fixtures already exist\n");
Illuminate\Support\Facades\Artisan::call('db:seed', ['--class'=>Database\Seeders\DatabaseSeeder::class,'--force'=>true]);
Illuminate\Support\Facades\Artisan::call('db:seed', ['--class'=>Database\Seeders\RolesAndPermissionsSeeder::class,'--force'=>true]);
Illuminate\Support\Facades\Artisan::call('db:seed', ['--class'=>Database\Seeders\TrainingScenarioSeeder::class,'--force'=>true]);
foreach (['cov_112'=>[900001,null], 'op_01'=>[900002,4], 'edds'=>[900003,8], 'gas'=>[900004,7]] as $role=>[$uid,$type]) {
 $user=App\Models\User::factory()->create(['uid'=>$uid,'name'=>'Учебный '.$role,'password'=>Illuminate\Support\Facades\Hash::make('ui-review-only'),'call_type_id'=>$type]);
 $user->assignRole($role);
}
$card=new App\Models\Incident(['call_type_id'=>4,'incoming_number'=>'Учебный','description'=>'Проверочная карточка','street'=>'Учебная улица','district_id'=>App\Models\District::value('id')]);
$card->user_id=App\Models\User::where('uid',900001)->value('id');$card->condition=App\Condition::CONNECTING;$card->save();
$fire=$card->fireReport()->create([]);$card->eddsReport()->create([]);$card->gasReport()->create([]);
foreach (App\Models\FirefighterDepartment::limit(2)->get() as $department) $fire->departments()->attach($department->id,['condition_id'=>1,'user_id'=>$card->user_id,'requested_at'=>'2026-11-10 12:00:00']);
echo "UI fixtures ready\n";
