<?php

namespace App\Policies;

use App\Models\TripUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripUserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TripUser $tripUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TripUser $tripUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TripUser $tripUser): bool
    {
        return $user->id === $tripUser->trip->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TripUser $tripUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TripUser $tripUser): bool
    {
        return false;
    }
}
