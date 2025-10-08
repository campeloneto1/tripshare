<?php

namespace App\Notifications;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TripReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Trip $trip
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $startDate = Carbon::parse($this->trip->start_date);
        $formattedDate = $startDate->format('d/m/Y');

        return [
            'type' => 'trip_reminder',
            'trip_id' => $this->trip->id,
            'trip_name' => $this->trip->name,
            'start_date' => $this->trip->start_date,
            'message' => "Sua viagem '{$this->trip->name}' começa amanhã ({$formattedDate})!"
        ];
    }
}
