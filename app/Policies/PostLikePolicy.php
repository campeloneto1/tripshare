<?php

namespace App\Policies;

use App\Models\PostLike;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostLikePolicy
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
    public function view(User $user, PostLike $postLike): bool
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
    public function update(User $user, PostLike $postLike): bool
    {
        // Likes não são editáveis, apenas criados e deletados
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PostLike $postLike): bool
    {
        return $postLike->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PostLike $postLike): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PostLike $postLike): bool
    {
        return $postLike->user_id === $user->id;
    }
}
