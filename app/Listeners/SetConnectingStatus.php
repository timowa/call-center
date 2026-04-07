<?php

namespace App\Listeners;

use App\Condition;
use App\Events\IncidentCardViewed;
use Illuminate\Support\Facades\Auth;

class SetConnectingStatus
{

    public function handle(IncidentCardViewed $event): void
    {
        $incident = $event->incident;
        $user = Auth::user();
        $changed = false;
        if ($incident->condition === Condition::REQUEST) {
            if ($user->hasRole('op_01') && $incident->fireReport->condition === Condition::REQUEST) {
                $incident->fireReport->condition = Condition::CONNECTING;
                $incident->fireReport->save();
                $changed = true;
            }
            if ($user->hasRole('edds') && $incident->eddsReport->condition === Condition::REQUEST) {
                $incident->eddsReport->condition = Condition::CONNECTING;
                $incident->eddsReport->save();
                $changed = true;
            }
        }

        if ($changed) {
            $incident->updateCondition();
        }
    }
}
