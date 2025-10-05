<?php

namespace App\Policies;

use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripDayCityPolicy
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
     * Verifica se o usuário pode gerenciar a viagem (dono ou admin)
     */
    private function canManageTrip(User $user, $trip): bool
    {
        return $user->id === $trip->user_id 
            || $trip->users()
                ->where('user_id', $user->id)
                ->where('role', 'admin')
                ->exists();
    }

    /**
     * Pode listar cidades do dia (qualquer usuário logado)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Pode ver a cidade se a viagem for pública ou se tiver acesso à viagem
     */
    public function view(User $user, TripDayCity $tripDayCity): bool
    {
        $trip = $tripDayCity->day->trip;
        return $trip->is_public || $this->canAccessTrip($user, $trip);
    }

    /**
     * Pode criar uma cidade no dia se for dono ou admin da viagem
     */
   public function create(User $user, TripDay $day): bool
    {
        $trip = $day->trip;
        return $this->canManageTrip($user, $trip);
    }

    /**
     * Pode atualizar se for dono ou admin da viagem
     */
    public function update(User $user, TripDayCity $tripDayCity): bool
    {
        return $this->canManageTrip($user, $tripDayCity->day->trip);
    }

    /**
     * Pode excluir se for dono ou admin da viagem
     */
    public function delete(User $user, TripDayCity $tripDayCity): bool
    {
        return $this->canManageTrip($user, $tripDayCity->day->trip);
    }

    /**
     * Restaurar - não permitido
     */
    public function restore(User $user, TripDayCity $tripDayCity): bool
    {
        return false;
    }

    /**
     * Excluir permanentemente - não permitido
     */
    public function forceDelete(User $user, TripDayCity $tripDayCity): bool
    {
        return false;
    }
}
