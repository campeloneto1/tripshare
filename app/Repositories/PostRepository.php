<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PostRepository
{
    /**
     * Retorna a query base.
     */
    public function baseQuery(): Builder
    {
        return Post::query();
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
     * Busca uma post pelo ID.
     */
    public function find(int $id): ?Post
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * Cria uma nova post.
     */
    public function create(array $data): Post
    {
        return Post::create($data);
    }

    /**
     * Atualiza uma post existente.
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post;
    }

    /**
     * Exclui uma post.
     */
    public function delete(Post $post): bool
    {
        return $post->delete();
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
