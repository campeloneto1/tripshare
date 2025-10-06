<?php

namespace App\Repositories;

use App\Models\PostLike;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PostLikeRepository
{
    /**
     * Retorna a query base.
     */
    public function baseQuery(): Builder
    {
        return PostLike::query();
    }

    /**
     * Lista posts com filtros e paginação.
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
     * Busca uma postLike pelo ID.
     */
    public function find(int $id): ?PostLike
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * Cria uma nova postLike.
     */
    public function create(array $data): PostLike
    {
        return PostLike::create($data);
    }

    /**
     * Atualiza uma postLike existente.
     */
    public function update(PostLike $postLike, array $data): PostLike
    {
        $postLike->update($data);
        return $postLike;
    }

    /**
     * Exclui uma post.
     */
    public function delete(PostLike $postLike): bool
    {
        return $postLike->delete();
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
