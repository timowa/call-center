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
        if (is_null($actionType)) {
            $condition = Condition::REQUEST;
        } else {
            $condition = Condition::DONE;
        }
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
