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
        if (in_array($incident->call_type_id, [CallType::HELP, CallType::FALSE, CallType::CHILD])) {
            $incident->condition = Condition::DONE;
            $incident->save();

            if (!is_null($incident->fireReport)) {
                $incident->fireReport->condition = Condition::DONE;
                $incident->fireReport->save();
            }

            if (!is_null($incident->eddsReport)) {
                $incident->eddsReport->condition = Condition::DONE;
                $incident->eddsReport->save();
            }

        }

    }
}
