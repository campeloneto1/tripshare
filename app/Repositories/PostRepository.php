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
            $query->where('content', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['trip_id'])) {
            $query->where('trip_id', $filters['trip_id']);
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
}
