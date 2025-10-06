<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PostComment;
use App\Repositories\PostCommentRepository;

class PostCommentService
{
    public function __construct(private PostCommentRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?PostComment
    {
        $post = $this->repository->find($id);
        return $post;
    }

    public function store(array $data): PostComment
    {
        return DB::transaction(function () use ($data) {
            // Valida se o parent_id existe e pertence ao mesmo post
            if (!empty($data['parent_id'])) {
                $parent = $this->repository->find($data['parent_id']);
                if (!$parent) {
                    throw new \InvalidArgumentException('Comentário pai não encontrado.');
                }
                if ($parent->post_id !== $data['post_id']) {
                    throw new \InvalidArgumentException('Comentário pai não pertence a este post.');
                }
            }

            return $this->repository->create($data);
        });
    }

    public function update(PostComment $postComment, array $data): PostComment
    {
        return DB::transaction(function () use ($postComment, $data) {
            return $this->repository->update($postComment, $data);
        });
    }

    public function delete(PostComment $postComment): bool
    {
        return DB::transaction(fn() => $this->repository->delete($postComment));
    }

   
}
