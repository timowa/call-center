<?php

namespace App\Listeners;

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
        if ($incident->condition === Condition::DONE->value)
        $incident->load('fireReport');
        if (!is_null($incident->fireReport)) {
            $incident->fireReport->update(['condition' => Condition::DONE]);
        }
    }
}
