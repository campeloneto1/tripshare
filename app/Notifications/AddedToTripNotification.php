<?php

namespace App\Notifications;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AddedToTripNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Trip $trip,
        public User $addedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'added_to_trip',
            'trip_id' => $this->trip->id,
            'trip_name' => $this->trip->name,
            'added_by_id' => $this->addedBy->id,
            'added_by_name' => $this->addedBy->name,
            'added_by_username' => $this->addedBy->username,
            'added_by_avatar' => $this->addedBy->getAvatar(),
            'message' => "{$this->addedBy->name} adicionou você na trip '{$this->trip->name}'"
        ];
    }
}
