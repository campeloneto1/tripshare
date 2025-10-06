<?php

namespace App\Observers;

use App\Models\TripDay;
use App\Models\TripHistory;

class TripDayObserver
{
    public function created(TripDay $tripDay): void
    {
        TripHistory::create([
            'trip_id' => $tripDay->trip_id,
            'user_id' => auth()->id(),
            'action' => 'day_added',
            'model_type' => TripDay::class,
            'model_id' => $tripDay->id,
            'description' => 'Dia adicionado: ' . $tripDay->date->format('d/m/Y'),
        ]);
    }

    public function updated(TripDay $tripDay): void
    {
        $changes = [];
        $descriptions = [];

        foreach ($tripDay->getDirty() as $key => $value) {
            $original = $tripDay->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => $value,
            ];

            if ($key === 'date') {
                $descriptions[] = "data alterada de {$original} para {$value}";
            }
        }

        if (!empty($changes)) {
            TripHistory::create([
                'trip_id' => $tripDay->trip_id,
                'user_id' => auth()->id(),
                'action' => 'day_updated',
                'model_type' => TripDay::class,
                'model_id' => $tripDay->id,
                'changes' => $changes,
                'description' => 'Dia ' . $tripDay->date->format('d/m/Y') . ': ' . implode(', ', $descriptions),
            ]);
        }
    }

    public function deleted(TripDay $tripDay): void
    {
        TripHistory::create([
            'trip_id' => $tripDay->trip_id,
            'user_id' => auth()->id(),
            'action' => 'day_removed',
            'model_type' => TripDay::class,
            'model_id' => $tripDay->id,
            'description' => 'Dia removido: ' . $tripDay->date->format('d/m/Y'),
        ]);
    }

    public function restored(TripDay $tripDay): void
    {
        //
    }

    public function forceDeleted(TripDay $tripDay): void
    {
        //
    }
}
