<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoteAnswer;

class VoteAnswerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VoteAnswer $voteAnswer): bool
    {
        // Usuários da trip podem ver respostas
        return $user->id === $voteAnswer->question->trip->user_id
            || $user->hasPermission('administrator');
    }

    public function create(User $user): bool
    {
        // Usuários podem votar
        return true;
    }

    public function update(User $user, VoteAnswer $voteAnswer): bool
    {
        // Apenas admin pode alterar respostas
        return $user->hasPermission('administrator');
    }

    public function delete(User $user, VoteAnswer $voteAnswer): bool
    {
        return $user->hasPermission('administrator');
    }
}
