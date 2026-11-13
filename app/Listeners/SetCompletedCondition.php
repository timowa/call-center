<?php

namespace App\Listeners;

use App\CallType;
use App\Condition;
use App\Events\IncidentUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class SetCompletedCondition
{

    public function handle(IncidentUpdated $event): void
    {
        $incident = $event->incident;
        $countRelated = 0;
        $countRelatedInStatus = 0;

        if ($incident->fireReport()->exists()) {
            $countRelated++;
            if ($incident->fireReport->condition === Condition::DONE) {
                $countRelatedInStatus++;
            }
        }
        if ($incident->eddsReport()->exists()) {
            $countRelated++;
            if ($incident->eddsReport->condition === Condition::DONE) {
                $countRelatedInStatus++;
            }
        }
        if ($incident->gasReport()->exists()) {
            $countRelated++;
            if ($incident->gasReport->condition === Condition::DONE) {
                $countRelatedInStatus++;
            }
        }
        if ($countRelated > 0 && $countRelated === $countRelatedInStatus) {
            $incident->condition = Condition::DONE;
            $incident->save();
        }

    }
}
