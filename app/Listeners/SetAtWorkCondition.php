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
        if ($incident->condition !== Condition::RESPONSE) {
            return;
        }

        $countRelated = 0;
        $countRelatedInStatus = 0;

        if ($incident->fireReport()->exists()) {
            $countRelated++;
            if ($incident->fireReport->condition === Condition::AT_WORK) {
                $countRelatedInStatus++;
            }
        }
        if ($incident->eddsReport()->exists()) {
            $countRelated++;
            if ($incident->eddsReport->condition === Condition::AT_WORK) {
                $countRelatedInStatus++;
            }
        }
        if ($countRelated === $countRelatedInStatus) {
            $incident->condition = Condition::AT_WORK;
            $incident->save();
        }
    }
}
