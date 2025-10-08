<?php

namespace App\Jobs;

use App\Models\Trip;
use App\Models\User;
use App\Notifications\TransportReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTransportReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Trip $trip;
    protected User $user;
    protected string $transportType;
    protected string $transportDatetime;

    /**
     * Create a new job instance.
     */
    public function __construct(Trip $trip, User $user, string $transportType, string $transportDatetime)
    {
        $this->trip = $trip;
        $this->user = $user;
        $this->transportType = $transportType;
        $this->transportDatetime = $transportDatetime;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(
            new TransportReminderNotification($this->trip, $this->transportType, $this->transportDatetime)
        );
    }
}
