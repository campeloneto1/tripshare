<?php

namespace App\Repositories;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TripRepository
{
    public function baseQuery()
    {
        $query = Trip::query();
        return $query;
    }

    public function all(array $filters)
    {
        $user = Auth::user();
        $query = $this->baseQuery();

        if(!empty($filters['search'])){
            $this->filterSearch($query, $filters['search']);
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        if(!$user->hasPermission('administrator')){
            $query->where('user_id', $user->id);
        }
        
        return $query->get();
    }
    public function find(int $id): ?Trip
    {
        return $this->baseQuery()->find($id);
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

     public function filterSearch(Builder $query, string $search)
    {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('cpf', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%");
        });
    }
}
