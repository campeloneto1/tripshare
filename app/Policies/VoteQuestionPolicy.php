<?php

namespace App\Policies;

use App\Models\Trip;
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
        // Pode ver se o usuário participa da trip relacionada ao votable
        $trip = $this->getTripFromVotable($voteQuestion);
        return $trip && ($user->id === $trip->user_id || $user->hasPermission('administrator'));
    }

    public function create(User $user): bool
    {
        // Pode criar se for admin ou participante de trip
        return true;
    }

    public function update(User $user, VoteQuestion $voteQuestion): bool
    {
        $trip = $this->getTripFromVotable($voteQuestion);
        return $trip && ($user->id === $trip->user_id || $user->hasPermission('administrator'));
    }

    public function delete(User $user, VoteQuestion $voteQuestion): bool
    {
        $trip = $this->getTripFromVotable($voteQuestion);
        return $trip && ($user->id === $trip->user_id || $user->hasPermission('administrator'));
    }

    private function getTripFromVotable(VoteQuestion $voteQuestion): ?Trip
    {
        $votable = $voteQuestion->votable;

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
