<?php

namespace App\Repositories;

use App\Models\TripDay;

class TripDayRepository
{
    public function all()
    {
        return TripDay::query(); 
    }

    public function find(int $id): ?TripDay
    {
        return TripDay::find($id);
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
}
