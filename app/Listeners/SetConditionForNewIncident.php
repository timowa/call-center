<?php

namespace App\Listeners;

use App\Condition;
use App\Events\IncidentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetConditionForNewIncident
{


    public function handle(IncidentCreated $event): void
    {
        $incident = $event->incident;
        $actionType = $event->actionType;
        $incident->load('fireReport');
        if ($incident->call_type_id->hasRelatedService()) {
            $condition = Condition::CONNECTING;
        } else {
            $condition = Condition::DONE;
        }
        if ($incident->gasReport) { $incident->gasReport->condition = $condition; $incident->gasReport->save(); }
        $incident->condition = $condition;
        $incident->save();
        if (!is_null($incident->fireReport)) {
            $incident->fireReport->condition = $condition;
            $incident->fireReport->save();
        }

        if (!is_null($incident->eddsReport)) {
            $incident->eddsReport->condition = $condition;
            $incident->eddsReport->save();
        }

    }
}
