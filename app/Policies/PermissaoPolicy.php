<?php

namespace App\Policies;

use App\Models\Permissao;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PermissaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissao('administrador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permissao $permissao): bool
    {
        return $user->hasPermissao('administrador');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissao('administrador');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permissao $permissao): bool
    {
        return $user->hasPermissao('administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permissao $permissao): bool
    {
        return $user->hasPermissao('administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permissao $permissao): bool
    {
        return $user->hasPermissao('administrador');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permissao $permissao): bool
    {
        return $user->hasPermissao('administrador');
    }
}
