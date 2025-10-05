<?php

namespace App\Repositories;

use App\Models\TripUser;

class TripUserRepository
{
    public function baseQuery(){
        return TripUser::query();
    }

    public function all()
    {
        return $this->baseQuery(); 
    }

    public function find(int $id): ?TripUser
    {
        return $this->baseQuery()->find($id);
    }

    public function create(array $data): TripUser
    {
        return TripUser::create($data);
    }

    public function update(TripUser $tripUser, array $data): TripUser
    {
        $tripUser->update($data);
        return $tripUser;
    }

    public function delete(TripUser $tripUser): bool
    {
        return $tripUser->delete();
    }

    public function insert(array $data): void
    {
        TripUser::insert($data);
    }

    public function where(string $column, $value)
    {
        return $this->all()->where($column, $value);
    }
}
