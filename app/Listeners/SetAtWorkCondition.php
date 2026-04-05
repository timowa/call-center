<?php

namespace App\Listeners;

use App\Condition;
use App\Events\IncidentUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class SetAtWorkCondition
{
    public function handle(IncidentUpdated $event): void
    {
        $incident = $event->incident;
        $user = Auth::user();
        if ($user->hasRole('op_01')) {
            $incident->load('fireReport');
            $fireReport = $incident->fireReport;
            if (!is_null($fireReport) && $fireReport->condition === Condition::RESPONSE->value) {
                $fieldsWasUpdated = false;
                if (!is_null($fireReport->dead_total)
                    || !is_null($fireReport->dead_children)
                    || !is_null($fireReport->dead_staff)
                    || !is_null($fireReport->injured_total)
                    || !is_null($fireReport->injured_children)
                    || !is_null($fireReport->injured_staff)
                    || !is_null($fireReport->rescued_children)
                    || !is_null($fireReport->rescued_staff)
                    || !is_null($fireReport->violated_children)
                    || !is_null($fireReport->violated_staff)
                    || !is_null($fireReport->cause_id)
                    || !is_null($fireReport->localized_at)
                    || !is_null($fireReport->fire_eliminated_at)
                    || !is_null($fireReport->elimination_at)
                    || !empty($fireReport->info)
                )  {
                    $fieldsWasUpdated = true;
                }
                if ($fieldsWasUpdated) {
                    $incident->fireReport->update(['condition' => Condition::AT_WORK]);
                    $incident->update(['condition' => Condition::AT_WORK]);
                }
            }
        }
    }
}
