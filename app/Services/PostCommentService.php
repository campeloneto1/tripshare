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
