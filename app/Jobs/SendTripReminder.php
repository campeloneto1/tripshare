<?php

namespace App\Jobs;

use App\Models\Trip;
use App\Models\User;
use App\Notifications\TripReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendTripReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Trip $trip;

    /**
     * Create a new job instance.
     */
    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
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
            new TripReminderNotification($this->trip)
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
