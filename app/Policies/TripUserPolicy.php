<?php

namespace App\Policies;

use App\Models\TripUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripUserPolicy
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
     * Pode listar usuários da viagem (qualquer usuário logado)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Pode visualizar se a viagem for pública ou o usuário tiver acesso
     */
    public function view(User $user, TripUser $tripUser): bool
    {
        $trip = $tripUser->trip;
        return $trip->is_public || $this->canAccessTrip($user, $trip);
    }

    /**
     * Pode adicionar um usuário à viagem se for dono ou admin
     */
    public function create(User $user, TripUser $tripUser): bool
    {
        $trip = $tripUser->trip;
        return $this->canManageTrip($user, $trip);
    }

    /**
     * Pode atualizar informações do usuário na viagem (ex: mudar role) se for dono ou admin
     */
    public function update(User $user, TripUser $tripUser): bool
    {
        return $this->canManageTrip($user, $tripUser->trip);
    }

    /**
     * Pode remover um usuário da viagem se for dono ou admin
     */
    public function delete(User $user, TripUser $tripUser): bool
    {
        return $this->canManageTrip($user, $tripUser->trip);
    }

    /**
     * Restaurar - não permitido
     */
    public function restore(User $user, TripUser $tripUser): bool
    {
        return false;
    }

    /**
     * Excluir permanentemente - não permitido
     */
    public function forceDelete(User $user, TripUser $tripUser): bool
    {
        return false;
    }
}
