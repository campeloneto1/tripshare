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

        // Agenda lembrete 3 dias antes do início (fazer check-in)
        $threeDaysBeforeStart = $startDate->copy()->subDays(3)->startOfDay();
        if ($threeDaysBeforeStart->isFuture()) {
            SendTripCheckInReminder::dispatch($this->trip, 'before_start')
                ->delay($threeDaysBeforeStart);
        }

        // Agenda lembrete 3 dias antes do fim (fazer check-out)
        $threeDaysBeforeEnd = $endDate->copy()->subDays(3)->startOfDay();
        if ($threeDaysBeforeEnd->isFuture()) {
            SendTripCheckInReminder::dispatch($this->trip, 'before_end')
                ->delay($threeDaysBeforeEnd);
        }
    }
}
