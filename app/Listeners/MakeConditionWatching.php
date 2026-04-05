<?php

namespace App\Listeners;

use App\Condition;
use App\Events\IncidentCardViewed;
use Illuminate\Support\Facades\Auth;

class MakeConditionWatching
{

    public function handle(IncidentCardViewed $event): void
    {
        $incident = $event->incident;
        $user = Auth::user();

        if ($user->hasRole('op_01')) {
            $incident->load('fireReport');
            if (!is_null($incident->fireReport) && Condition::canSwitchToWatching($incident->fireReport->condition)) {
                $incident->fireReport()->update(['condition' => Condition::WATCHING]);
                $incident->update(['condition' => Condition::WATCHING]);
            }
        }
    }
}
