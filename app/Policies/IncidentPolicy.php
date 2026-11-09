<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function create(User $user): bool
    {
        return $user->can('ukio.create');
    }

    public function view(User $user, Incident $incident): bool
    {
        $inArea = !$user->area_id || (int) $incident->district?->area_id === (int) $user->area_id || $incident->user_id === $user->id;
        return $inArea && $user->can('ukio.view') &&
            (!$user->hasRole('op_01') || $incident->fireReport()->exists()) &&
            (!$user->hasRole('edds') || $incident->eddsReport()->exists());
    }

    public function update(User $user, Incident $incident): bool
    {
        return $this->view($user, $incident) &&
            ($user->can('ukio.edit') || $user->can('ukio.edit-description') || $user->can('01.edit') || $user->can('edds.edit'));
    }
}
