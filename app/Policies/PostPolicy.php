<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
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
    public function view(User $user, Post $post): bool
    {
        // É o autor - sempre pode ver
        if ($post->user_id === $user->id) {
            return true;
        }

        // Carrega o autor do post
        $author = $post->user;

        // Verifica privacidade do autor: se o autor é privado, precisa estar seguindo
        if ($author && !$author->is_public) {
            // Verifica se está seguindo o autor (status accepted)
            $isFollowing = $author->followers()
                ->where('follower_id', $user->id)
                ->exists();

            if (!$isFollowing) {
                return false; // Autor é privado e não está seguindo
            }
        }

        // Se o post está vinculado a uma trip, verifica acesso à trip
        if ($post->trip_id) {
            $trip = $post->trip;

            // Se trip é pública ou é membro da trip, pode ver
            return $trip && ($trip->is_public || $trip->users()->where('user_id', $user->id)->exists());
        }

        // Post público (sem trip_id) e autor público (ou já seguindo)
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
    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }
}
