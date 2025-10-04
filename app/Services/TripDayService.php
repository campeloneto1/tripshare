<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\TripDay;
use App\Repositories\TripDayRepository;

class TripDayService
{
    public function __construct(private TripDayRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?TripDay
    {
        return $this->repository->find($id);
    }

    public function store(array $data): TripDay
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(TripDay $tripDay, array $data): TripDay
    {
        return DB::transaction(function () use ($tripDay, $data) {
            return $this->repository->update($tripDay, $data);
        });
    }

    public function delete(TripDay $tripDay): bool
    {
        return DB::transaction(fn() => $this->repository->delete($tripDay));
    }

    public function listByTrip(int $tripId)
    {
        return $this->repository->where('trip_id', $tripId)->get();
    }
}
