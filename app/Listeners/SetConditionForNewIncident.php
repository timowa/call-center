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
        $incident->load('fireReport');
        if (is_null($incident->fireReport)) {
            $incident->update(['condition' => Condition::REQUEST]);
        } else {
            $incident->update(['condition' => Condition::CONNECTING]);
            $incident->fireReport()->update(['condition' => Condition::CONNECTING]);
        }

    }
}
