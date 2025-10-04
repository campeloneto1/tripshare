<?php

namespace App\Repositories;

use App\Models\TripDayCity;

class TripDayCityRepository
{
    public function baseQuery(){
        return TripDayCity::query();
    }

    public function all()
    {
        return $this->baseQuery(); 
    }

    public function find(int $id): ?TripDayCity
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): TripDayCity
    {
        return TripDayCity::create($data);
    }

    public function update(TripDayCity $tripDayCity, array $data): TripDayCity
    {
        $tripDayCity->update($data);
        return $tripDayCity;
    }

    public function delete(TripDayCity $tripDayCity): bool
    {
        return $tripDayCity->delete();
    }

     public function where(string $column, $value)
    {
        return $this->all()->where($column, $value);
    }

}
