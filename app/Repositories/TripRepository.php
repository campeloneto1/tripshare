<?php

namespace App\Repositories;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TripRepository
{
    public function baseQuery()
    {
        return Trip::query()->withRelations();
    }

    public function all(array $filters)
    {
        $user = Auth::user();
        $query = $this->baseQuery()->withCount(['users', 'days']);

        if(!empty($filters['search'])){
            $this->filterSearch($query, $filters['search']);
        }

        // Filtro de trips acessíveis
        if(!$user->hasPermission('administrator')){
            $query->accessibleBy($user->id);
        }

        // Filtros adicionais
        if (!empty($filters['status']) && $filters['status'] === 'active') {
            $query->active();
        }

        if (!empty($filters['public_only'])) {
            $query->public();
        }

        // Ordenação
        $query->orderByStartDate('desc');

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    /**
     * Busca trips do usuário com cache
     */
    public function getTripsForUser(int $userId, bool $includeParticipating = true)
    {
        $cacheKey = "user_trips_{$userId}_participating_" . ($includeParticipating ? '1' : '0');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $includeParticipating) {
            $query = Trip::query()
                ->withRelations()
                ->withCount(['users', 'days']);

            if ($includeParticipating) {
                $query->accessibleBy($userId);
            } else {
                $query->forUser($userId);
            }

            return $query->orderByStartDate('desc')->get();
        });
    }

    /**
     * Invalida cache de trips do usuário
     */
    public function clearUserTripsCache(int $userId): void
    {
        Cache::forget("user_trips_{$userId}_participating_1");
        Cache::forget("user_trips_{$userId}_participating_0");
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
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
