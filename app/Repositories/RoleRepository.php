<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository
{
    /**
     * Retorna a query base.
     */
    public function baseQuery(): Builder
    {
        return Role::query();
    }

    /**
     * Lista roles com filtros e paginação.
     */
    public function all(array $filters = [])
    {
        $query = $this->baseQuery();

        if (!empty($filters['search'])) {
            $this->filterSearch($query, $filters['search']);
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    /**
     * Busca uma role pelo ID.
     */
    public function find(int $id): ?Role
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * Cria uma nova role.
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Atualiza uma role existente.
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    /**
     * Exclui uma role.
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Aplica filtro de busca por nome ou descrição.
     */
    protected function filterSearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
