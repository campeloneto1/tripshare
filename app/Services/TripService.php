<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Repositories\TripRepository;

class TripService
{
    public function __construct(private TripRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?Trip
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Trip
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
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
}
