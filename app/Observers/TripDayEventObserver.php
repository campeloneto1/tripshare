<?php

namespace App\Observers;

use App\Models\TripDayEvent;
use App\Models\TripHistory;

class TripDayEventObserver
{
    public function created(TripDayEvent $event): void
    {
        $tripId = $event->city->day->trip_id;
        $date = $event->city->day->date->format('d/m/Y');

        $typeNames = [
            'hotel' => 'Hotel',
            'restaurant' => 'Restaurante',
            'attraction' => 'Atração',
            'transport' => 'Transporte',
            'other' => 'Outro',
        ];

        $typeName = $typeNames[$event->type] ?? $event->type;

        TripHistory::create([
            'trip_id' => $tripId,
            'user_id' => auth()->id(),
            'action' => 'event_added',
            'model_type' => TripDayEvent::class,
            'model_id' => $event->id,
            'description' => "{$typeName} '{$event->name}' adicionado ao dia {$date}",
        ]);
    }

    public function updated(TripDayEvent $event): void
    {
        $tripId = $event->city->day->trip_id;
        $changes = [];
        $descriptions = [];

        $fieldNames = [
            'name' => 'nome',
            'type' => 'tipo',
            'start_time' => 'horário de início',
            'end_time' => 'horário de fim',
            'price' => 'preço',
            'notes' => 'notas',
            'order' => 'ordem',
        ];

        foreach ($event->getDirty() as $key => $value) {
            $original = $event->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => $value,
            ];

            $fieldName = $fieldNames[$key] ?? $key;
            $descriptions[] = "{$fieldName} alterado de '{$original}' para '{$value}'";
        }

        if (!empty($changes)) {
            TripHistory::create([
                'trip_id' => $tripId,
                'user_id' => auth()->id(),
                'action' => 'event_updated',
                'model_type' => TripDayEvent::class,
                'model_id' => $event->id,
                'changes' => $changes,
                'description' => "Evento '{$event->name}': " . implode(', ', $descriptions),
            ]);
        }
    }

    public function deleted(TripDayEvent $event): void
    {
        $tripId = $event->city->day->trip_id;
        $date = $event->city->day->date->format('d/m/Y');

        TripHistory::create([
            'trip_id' => $tripId,
            'user_id' => auth()->id(),
            'action' => 'event_removed',
            'model_type' => TripDayEvent::class,
            'model_id' => $event->id,
            'description' => "Evento '{$event->name}' removido do dia {$date}",
        ]);
    }

    public function restored(TripDayEvent $tripDayEvent): void
    {
        //
    }

    public function forceDeleted(TripDayEvent $tripDayEvent): void
    {
        //
    }
}
