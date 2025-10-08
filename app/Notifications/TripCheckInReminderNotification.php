<?php

namespace App\Notifications;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TripCheckInReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Trip $trip,
        public string $reminderType // 'before_start' ou 'before_end'
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isCheckIn = $this->reminderType === 'before_start';
        $date = $isCheckIn ? $this->trip->start_date : $this->trip->end_date;
        $formattedDate = Carbon::parse($date)->format('d/m/Y');

        $message = $isCheckIn
            ? "Lembrete: Fazer check-in do voo para a viagem '{$this->trip->name}' (início em {$formattedDate})"
            : "Lembrete: Fazer check-in do voo de volta da viagem '{$this->trip->name}' (retorno em {$formattedDate})";

        return [
            'type' => 'trip_flight_checkin_reminder',
            'trip_id' => $this->trip->id,
            'trip_name' => $this->trip->name,
            'reminder_type' => $this->reminderType,
            'date' => $date,
            'message' => $message
        ];
    }
}
