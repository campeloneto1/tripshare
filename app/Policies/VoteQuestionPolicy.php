<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoteQuestion;

class VoteQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        // Todos usuários autenticados podem listar perguntas
        return true;
    }

    public function view(User $user, VoteQuestion $voteQuestion): bool
    {
        // Pode ver se o usuário participa da trip
        return $user->id === $voteQuestion->trip->user_id
            || $user->hasPermission('administrator');
    }

    public function create(User $user): bool
    {
        // Pode criar se for dono da trip ou admin
        return $user->hasPermission('administrator');
    }

    public function update(User $user, VoteQuestion $voteQuestion): bool
    {
        return $user->id === $voteQuestion->trip->user_id
            || $user->hasPermission('administrator');
    }

    public function delete(User $user, VoteQuestion $voteQuestion): bool
    {
        return $user->id === $voteQuestion->trip->user_id
            || $user->hasPermission('administrator');
    }
}
