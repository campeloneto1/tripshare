<?php

namespace App\Observers;

use App\Models\TripDayCity;
use App\Models\TripHistory;

class TripDayCityObserver
{
    public function created(TripDayCity $tripDayCity): void
    {
        TripHistory::create([
            'trip_id' => $tripDayCity->day->trip_id,
            'user_id' => auth()->id(),
            'action' => 'city_added',
            'model_type' => TripDayCity::class,
            'model_id' => $tripDayCity->id,
            'description' => 'Cidade adicionada: ' . $tripDayCity->city_name,
        ]);
    }

    public function updated(TripDayCity $tripDayCity): void
    {
        $changes = [];
        $descriptions = [];

        foreach ($tripDayCity->getDirty() as $key => $value) {
            $original = $tripDayCity->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => $value,
            ];

            if ($key === 'city_name') {
                $descriptions[] = "nome alterado de {$original} para {$value}";
            } elseif ($key === 'order') {
                $descriptions[] = "ordem alterada de {$original} para {$value}";
            }
        }

        if (!empty($changes)) {
            TripHistory::create([
                'trip_id' => $tripDayCity->day->trip_id,
                'user_id' => auth()->id(),
                'action' => 'city_updated',
                'model_type' => TripDayCity::class,
                'model_id' => $tripDayCity->id,
                'changes' => $changes,
                'description' => 'Cidade ' . $tripDayCity->city_name . ': ' . implode(', ', $descriptions),
            ]);
        }
    }

    public function deleted(TripDayCity $tripDayCity): void
    {
        TripHistory::create([
            'trip_id' => $tripDayCity->day->trip_id,
            'user_id' => auth()->id(),
            'action' => 'city_removed',
            'model_type' => TripDayCity::class,
            'model_id' => $tripDayCity->id,
            'description' => 'Cidade removida: ' . $tripDayCity->city_name,
        ]);
    }

    public function restored(TripDayCity $tripDayCity): void
    {
        //
    }

    public function forceDeleted(TripDayCity $tripDayCity): void
    {
        //
    }
}
