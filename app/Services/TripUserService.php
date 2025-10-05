<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\TripUser;
use App\Models\User;
use App\Notifications\AddedToTripNotification;
use App\Repositories\TripUserRepository;
use Illuminate\Support\Facades\Auth;

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
            $tripUser = $this->repository->create($data);

            // Notifica o usuário adicionado
            $user = User::find($data['user_id']);
            $trip = Trip::find($data['trip_id']);
            $addedBy = Auth::user();

            if ($user && $trip && $addedBy && $user->id !== $addedBy->id) {
                $user->notify(new AddedToTripNotification($trip, $addedBy));
            }

            return $tripUser;
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
