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
            $return = $this->repository->create($data);
            $this->createTripDays($return);
            return $return;
        });
    }

    public function update(Trip $trip, array $data): Trip
    {
        return DB::transaction(function () use ($trip, $data) {
            return $this->repository->update($trip, $data);
        });
    }

    public function delete(Trip $trip): bool
    {
        return DB::transaction(fn() => $this->repository->delete($trip));
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
