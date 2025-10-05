<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Auth\Access\Response;

class UserFollowPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Qualquer usuário autenticado pode listar seguidores
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserFollow $userFollow): bool
    {
        // Usuário pode ver se ele é o seguidor ou o seguido
        return $user->id === $userFollow->follower_id ||
               $user->id === $userFollow->following_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Qualquer usuário autenticado pode seguir outros
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserFollow $userFollow): bool
    {
        // Apenas o seguido pode atualizar (aceitar/rejeitar)
        return $user->id === $userFollow->following_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserFollow $userFollow): bool
    {
        // O seguidor pode cancelar o follow ou o seguido pode remover
        return $user->id === $userFollow->follower_id ||
               $user->id === $userFollow->following_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserFollow $userFollow): bool
    {
        return $user->id === $userFollow->follower_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserFollow $userFollow): bool
    {
        return $user->id === $userFollow->follower_id;
    }
}
