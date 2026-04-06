<?php

namespace App\Listeners;

use App\Events\IncidentCreated;
use App\Models\Incident;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetConditionByCallTypeForNewIncident
{
    /**
     * Create the event listener.
     */
    public function __construct(public Incident $incident)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(IncidentCreated $event): void
    {
        $incident = $this->incident;

    }
}
