<?php

namespace App\Policies;

use App\Models\TripDay;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripDayPolicy
{
    /**
     * Verifica se o usuário tem acesso à viagem (dono ou participante)
     */
    private function canAccessTrip(User $user, $trip): bool
    {
        return $user->id === $trip->user_id 
            || $trip->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Verifica se o usuário pode editar a viagem (dono ou admin)
     */
    private function canManageTrip(User $user, $trip): bool
    {
        return $user->id === $trip->user_id 
            || $trip->users()
                ->where('user_id', $user->id)
                ->where('role', 'admin')
                ->exists();
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TripDay $tripDay): bool
    {
        $trip = $tripDay->trip;
        // Pode ver se: viagem é pública OU tem acesso à viagem
        return $trip->is_public || $this->canAccessTrip($user, $trip);
    }

    public function create(User $user): bool
    {
        // Será validado no controller verificando a trip
        return true;
    }

    public function update(User $user, TripDay $tripDay): bool
    {
        return $this->canManageTrip($user, $tripDay->trip);
    }

    public function delete(User $user, TripDay $tripDay): bool
    {
        return $this->canManageTrip($user, $tripDay->trip);
    }

    public function restore(User $user, TripDay $tripDay): bool
    {
        return $this->canManageTrip($user, $tripDay->trip);
    }

    public function forceDelete(User $user, TripDay $tripDay): bool
    {
        return $user->id === $tripDay->trip->user_id; // Só o dono
    }
}