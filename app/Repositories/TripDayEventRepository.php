<?php

namespace App\Repositories;

use App\Models\TripDayEvent;

class TripDayEventRepository
{
    public function baseQuery(){
        return TripDayEvent::query();
    }

    public function all()
    {
        return $this->baseQuery(); 
    }

    public function find(int $id): ?TripDayEvent
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): TripDayEvent
    {
        return TripDayEvent::create($data);
    }

    public function update(TripDayEvent $tripDayEvent, array $data): TripDayEvent
    {
        $tripDayEvent->update($data);
        return $tripDayEvent;
    }

    public function delete(TripDayEvent $tripDayEvent): bool
    {
        return $tripDayEvent->delete();
    }

     public function where(string $column, $value)
    {
        return $this->all()->where($column, $value);
    }

    public function hasTimeConflict(int $tripDayCityId, string $startTime, string $endTime, ?int $excludeEventId = null): bool
    {
        $query = $this->baseQuery()
            ->where('trip_day_city_id', $tripDayCityId)
            ->where(function ($q) use ($startTime, $endTime) {
                // Verifica se há sobreposição de horários
                $q->where(function ($subQ) use ($startTime, $endTime) {
                    // Evento começa durante outro evento
                    $subQ->where('start_time', '<=', $startTime)
                         ->where('end_time', '>', $startTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    // Evento termina durante outro evento
                    $subQ->where('start_time', '<', $endTime)
                         ->where('end_time', '>=', $endTime);
                })
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    // Evento engloba outro evento
                    $subQ->where('start_time', '>=', $startTime)
                         ->where('end_time', '<=', $endTime);
                });
            });

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        return $query->exists();
    }

}
