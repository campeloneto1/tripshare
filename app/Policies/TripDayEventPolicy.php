<?php

namespace App\Policies;

use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripDayEventPolicy
{
    private function canAccessTrip(User $user, $trip): bool
    {
        return $user->id === $trip->user_id 
            || $trip->users()->where('user_id', $user->id)->exists();
    }

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

    public function view(User $user, TripDayEvent $tripDayEvent): bool
    {
        $trip = $tripDayEvent->tripDay->trip;
        return $trip->is_public || $this->canAccessTrip($user, $trip);
    }

    public function create(User $user, TripDayCity $city): bool
    {
        $trip = $city->tripDay->trip;
        return $this->canManageTrip($user, $trip);
    }

    public function update(User $user, TripDayCity $city, TripDayEvent $event): bool
    {
        $trip = $city->tripDay->trip;
        return $this->canManageTrip($user, $trip);
    }

    public function delete(User $user, TripDayCity $city, TripDayEvent $event): bool
    {
        $trip = $city->tripDay->trip;
        return $this->canManageTrip($user, $trip);
    }

    public function restore(User $user, TripDayEvent $tripDayEvent): bool
    {
        return false;
    }

    public function forceDelete(User $user, TripDayEvent $tripDayEvent): bool
    {
        return false;
    }
}

