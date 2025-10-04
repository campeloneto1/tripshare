<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class PermissionRepository
{
    /**
     * Retorna a query base.
     */
    public function baseQuery(): Builder
    {
        return Permission::query();
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
    public function find(int $id): ?Permission
    {
        return Permission::find($id);
    }


    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission;
    }

    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }

     protected function filterSearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
