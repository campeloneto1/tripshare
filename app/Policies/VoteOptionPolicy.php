<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoteOption;

class VoteOptionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VoteOption $voteOption): bool
    {
        return $user->id === $voteOption->question->trip->user_id
            || $user->hasPermission('administrator');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('administrator');
    }

    public function update(User $user, VoteOption $voteOption): bool
    {
        return $user->id === $voteOption->question->trip->user_id
            || $user->hasPermission('administrator');
    }

    public function delete(User $user, VoteOption $voteOption): bool
    {
        return $user->id === $voteOption->question->trip->user_id
            || $user->hasPermission('administrator');
    }
}
