<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\TripDayEvent;
use App\Repositories\TripDayEventRepository;
use Illuminate\Support\Facades\Auth;

class TripDayEventService
{
    public function __construct(private TripDayEventRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?TripDayEvent
    {
        return $this->repository->find($id);
    }

    public function store(array $data): TripDayEvent
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            return $this->repository->create($data);
        });
    }

    public function update(TripDayEvent $tripDayEvent, array $data): TripDayEvent
    {
        return DB::transaction(function () use ($tripDayEvent, $data) {
             $data['updated_by'] = Auth::id();
            return $this->repository->update($tripDayEvent, $data);
        });
    }

    public function delete(TripDayEvent $tripDayEvent): bool
    {
        return DB::transaction(fn() => $this->repository->delete($tripDayEvent));
    }

    public function listByTripDayCity(int $tripId, int $dayId, int $cityId)
    {
        return $this->repository->where('trip_day_city_id', $cityId)->get();
    }

}
