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
use Illuminate\Support\Facades\Notification;

class SendTripCheckInReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Trip $trip;
    protected string $reminderType; // 'before_start' ou 'before_end'

    /**
     * Create a new job instance.
     */
    public function __construct(Trip $trip, string $reminderType)
    {
        $this->trip = $trip;
        $this->reminderType = $reminderType;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Busca todos participantes da trip
        $participants = $this->getTripParticipants();

        if ($participants->isEmpty()) {
            return;
        }

        // Envia notificação
        Notification::send(
            $participants,
            new TripCheckInReminderNotification($this->trip, $this->reminderType)
        );
    }

    private function getTripParticipants()
    {
        $participants = User::whereHas('trips', function ($query) {
            $query->where('trips.id', $this->trip->id);
        })->get();

        // Adiciona criador da trip se não estiver nos participantes
        if (!$participants->contains('id', $this->trip->user_id)) {
            $tripOwner = User::find($this->trip->user_id);
            if ($tripOwner) {
                $participants->push($tripOwner);
            }
        }

        return $participants;
    }
}
