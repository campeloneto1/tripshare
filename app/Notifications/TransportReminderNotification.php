<?php

namespace App\Notifications;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TransportReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transportTypeLabels = [
        'car' => 'Carro',
        'plane' => 'Avião',
        'bus' => 'Ônibus',
        'train' => 'Trem',
        'other' => 'Outro',
    ];

    public function __construct(
        public Trip $trip,
        public string $transportType,
        public string $transportDatetime
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $transportDate = Carbon::parse($this->transportDatetime);
        $formattedDate = $transportDate->format('d/m/Y');
        $formattedTime = $transportDate->format('H:i');
        $transportLabel = $this->transportTypeLabels[$this->transportType] ?? 'Transporte';

        return [
            'type' => 'transport_reminder',
            'trip_id' => $this->trip->id,
            'trip_name' => $this->trip->name,
            'transport_type' => $this->transportType,
            'transport_datetime' => $this->transportDatetime,
            'message' => "Lembrete: Seu {$transportLabel} para a viagem '{$this->trip->name}' sai em 2 horas ({$formattedDate} às {$formattedTime})"
        ];
    }
}
