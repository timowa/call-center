<?php

namespace App\Policies;

use App\Models\FireReport;
use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function update(User $user, Incident $incident)
    {
        if ($user->area_id && $user->area_id !== $incident->district->area_id) {
            return false;
        }

        if ($user->hasRole(['op_01', 'op_04'])) {
            if ($incident->user->role === $user->role) {
                return true;
            }
        }

        return true;
    }

}
