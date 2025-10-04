<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Viagem;
use Illuminate\Auth\Access\Response;

class ViagemPolicy
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
    public function view(User $user, Viagem $viagem): bool
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
    public function update(User $user, Viagem $viagem): bool
    {
        return $user->id === $viagem->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Viagem $viagem): bool
    {
        return $user->id === $viagem->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Viagem $viagem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Viagem $viagem): bool
    {
        return false;
    }
}
