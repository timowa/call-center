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
        $user = Auth::user();
        if ($user->hasRole('op_01')) {
            $incident->load('fireReport');
            if (!is_null($incident->fireReport) && $incident->fireReport->condition === Condition::WATCHING->value) {
                $incident->fireReport->update(['condition' => Condition::RESPONSE]);
                $incident->update(['condition' => Condition::RESPONSE]);
            }
        }
    }
}
