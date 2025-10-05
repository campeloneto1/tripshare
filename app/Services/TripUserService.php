<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\TripUser;
use App\Repositories\TripUserRepository;

class TripUserService
{
    public function __construct(private TripUserRepository $repository) {}

    public function list()
    {
        return $this->repository->all()->paginate(10);
    }

    public function find(int $id): ?TripUser
    {
        return $this->repository->find($id);
    }

    public function store(array $data): TripUser
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(TripUser $tripUser, array $data): TripUser
    {
        return DB::transaction(function () use ($tripUser, $data) {
            return $this->repository->update($tripUser, $data);
        });
    }

    public function delete(TripUser $tripUser): bool
    {
        return DB::transaction(fn() => $this->repository->delete($tripUser));
    }

    public function listByTrip(int $tripId)
    {
        return $this->repository->where('trip_id', $tripId)->get();
    }

}
