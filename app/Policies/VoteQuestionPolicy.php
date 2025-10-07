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

    public function create(User $user, $votableType = null, $votableId = null): bool
    {
        // Precisa receber o votable para verificar se é owner ou admin da trip
        // Se não receber, nega por segurança
        if (!$votableType || !$votableId) {
            return false;
        }

        $trip = null;

        // Se votable é TripDay
        if ($votableType === \App\Models\TripDay::class) {
            $tripDay = \App\Models\TripDay::find($votableId);
            $trip = $tripDay?->trip;
        }

        // Se votable é TripDayCity
        if ($votableType === \App\Models\TripDayCity::class) {
            $tripDayCity = \App\Models\TripDayCity::find($votableId);
            $trip = $tripDayCity?->tripDay?->trip;
        }

        if (!$trip) {
            return false;
        }

        // Verifica se é owner ou admin da trip
        return $user->id === $trip->user_id
            || $trip->users()
                ->where('user_id', $user->id)
                ->where('role', 'admin')
                ->exists();
    }

    public function update(User $user, VoteQuestion $voteQuestion): bool
    {
        if($voteQuestion->is_closed){
            return false;
        }
        $trip = $this->getTripFromVotable($voteQuestion);
        return $trip && ($user->id === $trip->user_id || $user->hasPermission('administrator'));
    }

    public function delete(User $user, VoteQuestion $voteQuestion): bool
    {
         if($voteQuestion->is_closed){
            return false;
        }
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
