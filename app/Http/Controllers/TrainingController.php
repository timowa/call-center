<?php
namespace App\Http\Controllers;
use App\Models\{Incident, TrainingSession, User};
use App\SourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Gate};
use Inertia\Inertia;
class TrainingController extends Controller {
 public function accept(Request $request) {
  Gate::authorize('create', Incident::class);
  $data = $request->validate(['kind' => 'required|in:call,sms,glonass', 'request_key' => 'required|uuid']);
  $session = DB::transaction(function () use ($request, $data) {
   User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
   $existing = TrainingSession::where('user_id',$request->user()->id)->where('request_key',$data['request_key'])->first();
   if ($existing) { abort_unless($existing->kind === $data['kind'], 409); return $existing; }
   $query = DB::table('training_scenarios')->where('kind',$data['kind'])->where('active',true);
   $cursor = DB::table('training_cursors')->where('user_id',$request->user()->id)->where('kind',$data['kind'])->first();
   $scenario = (clone $query)->where('id','>',$cursor?->last_scenario_id ?? 0)->orderBy('id')->first() ?? $query->orderBy('id')->first();
   if (!$scenario) throw \Illuminate\Validation\ValidationException::withMessages(['training' => 'Учебные сценарии не загружены.']);
   DB::table('training_cursors')->updateOrInsert(['user_id'=>$request->user()->id,'kind'=>$data['kind']],['last_scenario_id'=>$scenario->id]);
   $lines = preg_split('/\R/u',trim($scenario->content));
   return TrainingSession::create([
    'user_id'=>$request->user()->id, 'scenario_id'=>$scenario->id, 'request_key'=>$data['request_key'],
    'kind'=>$data['kind'], 'name'=>$scenario->name, 'content'=>$scenario->content,
    'messages'=>$data['kind']==='sms' ? [['direction'=>'incoming','text'=>$lines[0], 'at'=>now()->toDateTimeString()]] : [],
    'position'=>$data['kind']==='sms' ? 1 : 0,
   ]);
  });
  return redirect()->route($session->incident_id ? 'incidents.edit' : 'training.show', $session->incident_id ?? $session->id);
 }
 private function authorizeSession(TrainingSession $session, Request $request): void {
  if ($session->incident_id) Gate::authorize('view',$session->incident);
  else abort_unless($session->user_id === $request->user()->id && $request->user()->can('ukio.create'),403);
 }
 public function show(Request $request, int $id) {
  $session = TrainingSession::findOrFail($id); $this->authorizeSession($session,$request);
  if ($session->incident_id) return redirect()->route('incidents.edit',$session->incident_id);
  $source = match($session->kind) { 'call'=>SourceType::CALL, 'sms'=>SourceType::SMS, 'glonass'=>SourceType::GLONASS };
  return Inertia::render('Incidents/Edit',['mode'=>'create','incident'=>['source_id'=>$source->value,'incoming_number'=>'Учебный вызов','training_session'=>$session]]);
 }
 public function reply(Request $request, int $id) {
  $data=$request->validate(['request_key'=>'required|uuid','text'=>'required|string|max:2000']);
  return DB::transaction(function () use ($request,$id,$data) {
   $session=TrainingSession::lockForUpdate()->findOrFail($id); $this->authorizeSession($session,$request);
   abort_unless($session->user_id===$request->user()->id && $session->kind==='sms',403);
   $messages=$session->messages;
   if (collect($messages)->contains('request_key',$data['request_key'])) return response()->json($session);
   $lines=preg_split('/\R/u',trim($session->content));
   $messages[]=['direction'=>'outgoing','text'=>$data['text'],'request_key'=>$data['request_key'],'at'=>now()->toDateTimeString()];
   if ($session->position < count($lines)) {
    $messages[]=['direction'=>'incoming','text'=>$lines[$session->position],'at'=>now()->toDateTimeString()]; $session->position++;
   }
   $session->messages=$messages; $session->save(); return response()->json($session);
  });
 }
 public function hangup(Request $request,int $id) {
  $session=TrainingSession::findOrFail($id); $this->authorizeSession($session,$request);
  abort_unless($session->user_id===$request->user()->id && $session->kind==='call',403);
  $session->hung_up=true; $session->save(); return response()->json($session);
 }
}
