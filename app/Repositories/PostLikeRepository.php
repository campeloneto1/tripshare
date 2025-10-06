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
     * Lista likes com filtros e paginação.
     */
    public function all(array $filters = [])
    {
        $query = $this->baseQuery();

        if (!empty($filters['post_id'])) {
            $query->where('post_id', $filters['post_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
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
     * Busca like por post_id e user_id.
     */
    public function findByPostAndUser(int $postId, int $userId): ?PostLike
    {
        return $this->baseQuery()
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();
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
}
