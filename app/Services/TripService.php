<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Repositories\TripDayRepository;
use App\Repositories\TripRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TripService
{
    public function __construct(
        private TripRepository $repository,
        private TripDayRepository $tripDayRepository
        ) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?Trip
    {
        $return = $this->repository->find($id);
        return $return->load(['days']);
    }

    public function store(array $data): Trip
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = Auth::id();
            $data['created_by'] = Auth::id();
            $return = $this->repository->create($data);
            $this->createTripDays($return);

            // Invalida cache do usuário
            $this->repository->clearUserTripsCache(Auth::id());

            return $return;
        });
    }

    public function update(Trip $trip, array $data): Trip
    {
        return DB::transaction(function () use ($trip, $data) {
            $data['updated_by'] = Auth::id();
            $updatedTrip = $this->repository->update($trip, $data);

            // Invalida caches
            $updatedTrip->clearSummaryCache();
            $this->repository->clearUserTripsCache($trip->user_id);

            return $updatedTrip;
        });
    }

    public function delete(Trip $trip): bool
    {
        return DB::transaction(function() use ($trip) {
            $result = $this->repository->delete($trip);

            // Invalida cache do usuário
            $this->repository->clearUserTripsCache($trip->user_id);

            return $result;
        });
    }

    protected function createTripDays(Trip $trip): void
    {
        $startDate = Carbon::parse($trip->start_date);
        $endDate = Carbon::parse($trip->end_date);

        $days = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $days[] = [
                'trip_id' => $trip->id,
                'date' => $date->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $this->tripDayRepository->insert($days);
        
    }
}
