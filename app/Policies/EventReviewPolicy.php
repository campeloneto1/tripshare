<?php

namespace App\Policies;

use App\Models\EventReview;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EventReview $eventReview): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $tripDayEventId = null): bool
    {
        // Precisa verificar se o usuário é participante da trip do evento
        if (!$tripDayEventId) {
            return false;
        }

        $event = \App\Models\TripDayEvent::find($tripDayEventId);
        if (!$event) {
            return false;
        }

        $trip = $event->city?->day?->trip;
        if (!$trip) {
            return false;
        }

        // Verifica se é owner ou participante (admin ou participant)
        return $user->id === $trip->user_id
            || $trip->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EventReview $eventReview): bool
    {
        return $user->id === $eventReview->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EventReview $eventReview): bool
    {
        return $user->id === $eventReview->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EventReview $eventReview): bool
    {
        return $user->id === $eventReview->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EventReview $eventReview): bool
    {
        return $user->id === $eventReview->user_id;
    }
}
