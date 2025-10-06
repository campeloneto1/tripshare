<?php

namespace App\Repositories;

use App\Models\PostComment;
use Illuminate\Database\Eloquent\Builder;

class PostCommentRepository
{
    /**
     * Retorna a query base.
     */
    public function baseQuery(): Builder
    {
        return PostComment::query();
    }

    /**
     * Lista comentários com filtros e paginação.
     */
    public function all(array $filters = [])
    {
        $query = $this->baseQuery();

        if (!empty($filters['search'])) {
            $query->where('content', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['post_id'])) {
            $query->where('post_id', $filters['post_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if(!empty($filters['limit']) && is_numeric($filters['limit'])){
            return $query->paginate((int)$filters['limit']);
        }

        return $query->get();
    }

    /**
     * Busca uma PostComment pelo ID.
     */
    public function find(int $id): ?PostComment
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * Cria uma nova PostComment.
     */
    public function create(array $data): PostComment
    {
        return PostComment::create($data);
    }

    /**
     * Atualiza uma PostComment existente.
     */
    public function update(PostComment $postComment, array $data): PostComment
    {
        $postComment->update($data);
        return $postComment;
    }

    /**
     * Exclui uma post.
     */
    public function delete(PostComment $postComment): bool
    {
        return $postComment->delete();
    }
}
