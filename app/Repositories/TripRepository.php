<?php

namespace App\Repositories;

use App\Models\Trip;

class TripRepository
{
    public function all()
    {
        return Trip::query(); 
    }

    public function find(int $id): ?Trip
    {
        return Trip::find($id);
    }

    public function create(array $data): Trip
    {
        return Trip::create($data);
    }

    public function update(Trip $trip, array $data): Trip
    {
        $trip->update($data);
        return $trip;
    }

    public function delete(Trip $trip): bool
    {
        return $trip->delete();
    }
}
