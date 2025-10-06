<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PostRepository
{
    /**
     * Retorna a query base com eager loading otimizado.
     */
    public function baseQuery(): Builder
    {
        return Post::query()->withRelations();
    }

    /**
     * Lista posts com filtros e paginação.
     */
    public function all(array $filters = [])
    {
        $query = $this->baseQuery()
            ->withCount(['likes', 'comments'])
            ->recent();

        if (!empty($filters['search'])) {
            $query->where('content', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (!empty($filters['trip_id'])) {
            $query->forTrip($filters['trip_id']);
        }

        // Feed personalizado (apenas posts acessíveis)
        if (!empty($filters['accessible_by'])) {
            $query->accessibleBy($filters['accessible_by']);
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    /**
     * Busca posts do feed do usuário (com cache)
     */
    public function getFeedForUser(int $userId, int $limit = 20)
    {
        $cacheKey = "user_feed_{$userId}_limit_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $limit) {
            return Post::query()
                ->withFullRelations()
                ->accessibleBy($userId)
                ->recent()
                ->limit($limit)
                ->get();
        });
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
