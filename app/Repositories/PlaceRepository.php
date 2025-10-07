<?php

namespace App\Repositories;

use App\Models\Place;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PlaceRepository
{
    /**
     * Retorna a query base com eager loading otimizado.
     */
    public function baseQuery(): Builder
    {
        return Place::query();
    }

    /**
     * Lista posts com filtros e paginação.
     */
    public function all(array $filters = [])
    {
        $query = $this->baseQuery();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    /**
     * Busca uma place pelo ID.
     */
    public function find(int $id): ?Place
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * Busca place por xid.
     */
    public function findByXid(string $xid): ?Place
    {
        return $this->baseQuery()->where('xid', $xid)->first();
    }

    /**
     * Cria uma nova place.
     */
    public function create(array $data): Place
    {
        return Place::create($data);
    }

    /**
     * Cria ou retorna place existente pelo xid.
     */
    public function firstOrCreate(array $attributes, array $values = []): Place
    {
        return Place::firstOrCreate($attributes, $values);
    }

    /**
     * Atualiza uma place existente.
     */
    public function update(Place $place, array $data): Place
    {
        $place->update($data);
        return $place;
    }

    /**
     * Exclui uma place.
     */
    public function delete(Place $place): bool
    {
        return $place->delete();
    }
}
