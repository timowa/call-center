<?php

namespace App\Listeners;

use App\Condition;
use App\Events\IncidentUpdated;
use Illuminate\Support\Facades\Auth;

class SetResponseCondition
{

    /**
     * Handle the event.
     */
    public function handle(IncidentUpdated $event): void
    {
        $incident = $event->incident;

        if (!in_array($incident->condition, [Condition::REQUEST, Condition::CONNECTING])) {
            return;
        }
        $countRelated = 0;
        $countRelatedInStatus = 0;

        if ($incident->fireReport()->exists()) {
            $countRelated++;
            if ($incident->fireReport->condition === Condition::RESPONSE) {
                $countRelatedInStatus++;
            }
        }
        if ($incident->eddsReport()->exists()) {
            $countRelated++;
            if ($incident->eddsReport->condition === Condition::RESPONSE) {
                $countRelatedInStatus++;
            }
        }
        if ($countRelated === $countRelatedInStatus) {
            $incident->condition = Condition::RESPONSE;
            $incident->save();
        }
    }
}
