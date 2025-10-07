<?php

namespace App\Repositories;

use App\Models\TripDayEvent;
use App\Services\PlaceService;

class TripDayEventRepository
{
    public function __construct(private PlaceService $placeService)
    {
    }
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
        // Se os dados do place vêm no array, cria/busca o place e substitui por place_id
        if (!empty($data['xid']) || !empty($data['place_data'])) {
            $placeData = $data['place_data'] ?? $data;
            $data['place_id'] = $this->placeService->createOrGetPlace($placeData);

            // Remove campos que não pertencem ao evento
            unset($data['place_data'], $data['name'], $data['type'], $data['lat'], $data['lon'], $data['xid'], $data['source_api'], $data['address'], $data['city'], $data['state'], $data['zip_code'], $data['country']);
        }

        return TripDayEvent::create($data);
    }

    public function update(TripDayEvent $tripDayEvent, array $data): TripDayEvent
    {
        // Se os dados do place vêm no array, cria/busca o place e substitui por place_id
        if (!empty($data['xid']) || !empty($data['place_data'])) {
            $placeData = $data['place_data'] ?? $data;
            $data['place_id'] = $this->placeService->createOrGetPlace($placeData);

            // Remove campos que não pertencem ao evento
            unset($data['place_data'], $data['name'], $data['type'], $data['lat'], $data['lon'], $data['xid'], $data['source_api'], $data['address'], $data['city'], $data['state'], $data['zip_code'], $data['country']);
        }

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
