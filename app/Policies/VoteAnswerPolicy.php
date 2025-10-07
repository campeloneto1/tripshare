<?php

namespace App\Policies;

use App\Models\Trip;
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
        $trip = $this->getTripFromAnswer($voteAnswer);
        return $trip && ($user->id === $trip->user_id || $user->hasPermission('administrator'));
    }

    public function create(User $user): bool
    {
        // Usuários podem votar
        return true;
    }

    public function update(User $user, VoteAnswer $voteAnswer): bool
    {
        // Apenas o próprio usuário ou admin pode alterar respostas
        return $user->id === $voteAnswer->user_id || $user->hasPermission('administrator');
    }

    public function delete(User $user, VoteAnswer $voteAnswer): bool
    {
        // Apenas o próprio usuário ou admin pode deletar respostas
        return $user->id === $voteAnswer->user_id || $user->hasPermission('administrator');
    }

    private function getTripFromAnswer(VoteAnswer $voteAnswer): ?Trip
    {
        $question = $voteAnswer->question;

        if (!$question) {
            return null;
        }

        $votable = $question->votable;

        if (!$votable) {
            return null;
        }

        // Se votable é TripDay, retorna trip diretamente
        if ($votable instanceof \App\Models\TripDay) {
            return $votable->trip;
        }

        // Se votable é TripDayCity, retorna trip através de TripDay
        if ($votable instanceof \App\Models\TripDayCity) {
            return $votable->tripDay?->trip;
        }

        return null;
    }
}
