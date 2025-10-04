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

}
