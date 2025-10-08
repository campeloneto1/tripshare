<?php

namespace App\Jobs;

use App\Models\Trip;
use App\Models\User;
use App\Notifications\TripCheckInReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTripCheckInReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Trip $trip;
    protected ?User $user; // Usuário específico ou null para buscar todos com avião
    protected string $reminderType; // 'before_transport' ou 'before_end'

    /**
     * Create a new job instance.
     */
    public function __construct(Trip $trip, ?User $user, string $reminderType)
    {
        $this->trip = $trip;
        $this->user = $user;
        $this->reminderType = $reminderType;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Se foi passado um usuário específico, envia só para ele
        if ($this->user) {
            $this->user->notify(
                new TripCheckInReminderNotification($this->trip, $this->reminderType)
            );
            return;
        }

        // Caso contrário, busca participantes com avião (para o lembrete de fim de viagem)
        if ($this->reminderType === 'before_end') {
            $participants = $this->getTripParticipantsWithPlane();

            foreach ($participants as $participant) {
                $participant->notify(
                    new TripCheckInReminderNotification($this->trip, $this->reminderType)
                );
            }
        }
    }

    private function getTripParticipantsWithPlane()
    {
        // Busca participantes que vão de avião
        $participants = User::whereHas('trips', function ($query) {
            $query->where('trips.id', $this->trip->id)
                  ->where('trips_users.transport_type', 'plane');
        })->get();

        return $participants;
    }
}
