<?php

namespace App\Repositories;

use App\Models\TripDay;

class TripDayRepository
{
    public function baseQuery(){
        return TripDay::query();
    }

    public function all()
    {
        return $this->baseQuery(); 
    }

    public function find(int $id): ?TripDay
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): TripDay
    {
        return TripDay::create($data);
    }

    public function update(TripDay $tripDay, array $data): TripDay
    {
        $tripDay->update($data);
        return $tripDay;
    }

    public function delete(TripDay $tripDay): bool
    {
        return $tripDay->delete();
    }

    public function insert(array $data): void
    {
        TripDay::insert($data);
    }

    public function where(string $column, $value)
    {
        return $this->all()->where($column, $value);
    }
}
