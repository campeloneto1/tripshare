<?php

namespace App\Observers;

use App\Models\Trip;
use App\Models\TripHistory;

class TripObserver
{
    /**
     * Handle the Trip "created" event.
     */
    public function created(Trip $trip): void
    {
        TripHistory::create([
            'trip_id' => $trip->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => Trip::class,
            'model_id' => $trip->id,
            'description' => 'Trip criada: ' . $trip->name,
        ]);
    }

    /**
     * Handle the Trip "updated" event.
     */
    public function updated(Trip $trip): void
    {
        $changes = [];
        $descriptions = [];

        foreach ($trip->getDirty() as $key => $value) {
            $original = $trip->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => $value,
            ];

            // Criar descrições legíveis
            $fieldNames = [
                'name' => 'nome',
                'description' => 'descrição',
                'start_date' => 'data de início',
                'end_date' => 'data de fim',
                'location' => 'localização',
            ];

            $fieldName = $fieldNames[$key] ?? $key;
            $descriptions[] = "{$fieldName} alterado de '{$original}' para '{$value}'";
        }

        if (!empty($changes)) {
            TripHistory::create([
                'trip_id' => $trip->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'model_type' => Trip::class,
                'model_id' => $trip->id,
                'changes' => $changes,
                'description' => 'Trip atualizada: ' . implode(', ', $descriptions),
            ]);
        }
    }

    /**
     * Handle the Trip "deleted" event.
     */
    public function deleted(Trip $trip): void
    {
        TripHistory::create([
            'trip_id' => $trip->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model_type' => Trip::class,
            'model_id' => $trip->id,
            'description' => 'Trip deletada: ' . $trip->name,
        ]);
    }

    /**
     * Handle the Trip "restored" event.
     */
    public function restored(Trip $trip): void
    {
        TripHistory::create([
            'trip_id' => $trip->id,
            'user_id' => auth()->id(),
            'action' => 'restored',
            'model_type' => Trip::class,
            'model_id' => $trip->id,
            'description' => 'Trip restaurada: ' . $trip->name,
        ]);
    }

    /**
     * Handle the Trip "force deleted" event.
     */
    public function forceDeleted(Trip $trip): void
    {
        //
    }
}
