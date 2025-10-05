<?php

namespace App\Policies;

use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripDayEventPolicy
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
     * Pode listar eventos (qualquer usuário logado)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Pode visualizar o evento se a viagem for pública ou o usuário tiver acesso à viagem
     */
    public function view(User $user, TripDayEvent $tripDayEvent): bool
    {
        $trip = $tripDayEvent->tripDay->trip;
        return $trip->is_public || $this->canAccessTrip($user, $trip);
    }

    /**
     * Pode criar um evento se for dono ou admin da viagem
     */
    public function create(User $user, TripDayEvent $tripDayEvent): bool
    {
        $trip = $tripDayEvent->tripDay->trip;
        return $this->canManageTrip($user, $trip);
    }

    /**
     * Pode atualizar o evento se for dono ou admin da viagem
     */
    public function update(User $user, TripDayEvent $tripDayEvent): bool
    {
        return $this->canManageTrip($user, $tripDayEvent->tripDay->trip);
    }

    /**
     * Pode excluir o evento se for dono ou admin da viagem
     */
    public function delete(User $user, TripDayEvent $tripDayEvent): bool
    {
        return $this->canManageTrip($user, $tripDayEvent->tripDay->trip);
    }

    /**
     * Restaurar - não permitido
     */
    public function restore(User $user, TripDayEvent $tripDayEvent): bool
    {
        return false;
    }

    /**
     * Excluir permanentemente - não permitido
     */
    public function forceDelete(User $user, TripDayEvent $tripDayEvent): bool
    {
        return false;
    }
}
