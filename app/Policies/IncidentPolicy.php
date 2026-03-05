<?php

namespace App\Policies;

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
        if ($user->district_id && $user->district_id !== $incident->district_id) {
            return false;
        }

        if ($user->hasRole(['op_01', 'op_04'])) {
            if ($incident->creator->role === $user->role) {
                return true;
            }
        }

        return true;
    }
}
