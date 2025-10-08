<?php

namespace App\Jobs;

use App\Models\Trip;
use App\Notifications\TripCheckInReminderNotification;
use App\Notifications\TripReminderNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduleTripReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Trip $trip;

    /**
     * Create a new job instance.
     */
    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($this->trip->start_date);
        $endDate = Carbon::parse($this->trip->end_date);

        // Agenda lembrete 1 dia antes da viagem
        $oneDayBeforeStart = $startDate->copy()->subDay()->startOfDay();
        if ($oneDayBeforeStart->isFuture()) {
            SendTripReminder::dispatch($this->trip)
                ->delay($oneDayBeforeStart);
        }

        // Busca participantes da viagem com informações de transporte
        $tripUsers = $this->trip->tripUsers()->with('user')->get();

        foreach ($tripUsers as $tripUser) {
            // Agenda check-in 3 dias antes APENAS para quem vai de avião
            if ($tripUser->transport_type === 'plane' && $tripUser->transport_datetime) {
                $transportDate = Carbon::parse($tripUser->transport_datetime);
                $threeDaysBeforeTransport = $transportDate->copy()->subDays(3);

                if ($threeDaysBeforeTransport->isFuture()) {
                    SendTripCheckInReminder::dispatch($this->trip, $tripUser->user, 'before_transport')
                        ->delay($threeDaysBeforeTransport);
                }
            }

            // Agenda lembrete 2 horas antes do transporte para TODOS que têm tipo e horário definidos
            if ($tripUser->transport_type && $tripUser->transport_datetime) {
                $transportDate = Carbon::parse($tripUser->transport_datetime);
                $twoHoursBeforeTransport = $transportDate->copy()->subHours(2);

                if ($twoHoursBeforeTransport->isFuture()) {
                    SendTransportReminder::dispatch($this->trip, $tripUser->user, $tripUser->transport_type, $tripUser->transport_datetime)
                        ->delay($twoHoursBeforeTransport);
                }
            }
        }

        // Agenda lembrete 3 dias antes do fim (fazer check-out) para quem vai de avião de volta
        $threeDaysBeforeEnd = $endDate->copy()->subDays(3)->startOfDay();
        if ($threeDaysBeforeEnd->isFuture()) {
            SendTripCheckInReminder::dispatch($this->trip, null, 'before_end')
                ->delay($threeDaysBeforeEnd);
        }
    }
}
