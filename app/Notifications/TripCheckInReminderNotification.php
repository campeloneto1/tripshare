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
        public string $reminderType // 'before_transport' ou 'before_end'
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = match($this->reminderType) {
            'before_transport' => $this->getTransportCheckInMessage(),
            'before_end' => $this->getReturnCheckInMessage(),
            default => "Lembrete de check-in para a viagem '{$this->trip->name}'"
        };

        return [
            'type' => 'trip_flight_checkin_reminder',
            'trip_id' => $this->trip->id,
            'trip_name' => $this->trip->name,
            'reminder_type' => $this->reminderType,
            'message' => $message
        ];
    }

    private function getTransportCheckInMessage(): string
    {
        return "Lembrete: Não esqueça de fazer o check-in do seu voo para a viagem '{$this->trip->name}'! Seu voo sai em 3 dias.";
    }

    private function getReturnCheckInMessage(): string
    {
        $endDate = Carbon::parse($this->trip->end_date)->format('d/m/Y');
        return "Lembrete: Não esqueça de fazer o check-in do seu voo de volta da viagem '{$this->trip->name}'! (retorno em {$endDate})";
    }
}
