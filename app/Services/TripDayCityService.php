<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\TripDayCity;
use App\Repositories\TripDayCityRepository;

class TripDayCityService
{
    public function __construct(private TripDayCityRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?TripDayCity
    {
        return $this->repository->find($id);
    }

    public function store(array $data): TripDayCity
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(TripDayCity $tripDayCity, array $data): TripDayCity
    {
        return DB::transaction(function () use ($tripDayCity, $data) {
            return $this->repository->update($tripDayCity, $data);
        });
    }

    public function delete(TripDayCity $tripDayCity): bool
    {
        return DB::transaction(fn() => $this->repository->delete($tripDayCity));
    }

     public function listByTripDay(int $tripId, int $dayId)
    {
        return $this->repository->where('trip_day_id', $dayId)->get();
    }

}
