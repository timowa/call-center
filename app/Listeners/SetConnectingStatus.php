<?php

namespace App\Listeners;

use App\Condition;
use App\Events\IncidentCardViewed;
use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetConnectingStatus
{
    public function handle(IncidentCardViewed $event): void
    {
        DB::transaction(function () use ($event) {
            $incident = Incident::lockForUpdate()->findOrFail($event->incident->id);
            $user = Auth::user();
            if ($user->hasRole('cov_112')) return;
            $viewed = false;
            foreach (['op_01' => 'fireReport', 'edds' => 'eddsReport', 'gas' => 'gasReport'] as $role => $relation) {
                if (!$user->hasRole($role)) continue;
                $report = $incident->$relation;
                if ($report?->condition === Condition::CONNECTING) {
                    $report->condition = Condition::WATCHING;
                    $report->save();
                    $viewed = true;
                }
            }
            if ($viewed && $incident->condition === Condition::CONNECTING) {
                $incident->condition = Condition::WATCHING;
                $incident->save();
            }
            $event->incident->refresh();
        });
    }
}
