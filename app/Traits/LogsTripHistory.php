<?php

namespace App\Traits;

use App\Models\TripHistory;

trait LogsTripHistory
{
    /**
     * Log uma ação no histórico da trip
     */
    public function logTripHistory(string $action, string $description, ?array $changes = null): void
    {
        TripHistory::create([
            'trip_id' => $this->trip_id ?? $this->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => static::class,
            'model_id' => $this->id,
            'changes' => $changes,
            'description' => $description,
        ]);
    }
}
