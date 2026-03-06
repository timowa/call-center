<?php

namespace App\Policies;

use App\Models\FireReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FireReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FireReport $fireReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FireReport $fireReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FireReport $fireReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FireReport $fireReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FireReport $fireReport): bool
    {
        return false;
    }

    public function changeStatus(User $user, FireReport $fireReport): bool
    {
        return $user->hasRole('op_01');
    }

    public function setStatusConnected(User $user, FireReport $fireReport): bool
    {
        return $this->changeStatus($user, $fireReport) && $fireReport->condition === 1;
    }
}
