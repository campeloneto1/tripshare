<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Repositories\PostRepository;

class PostService
{
    public function __construct(private PostRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?Post
    {
        $post = $this->repository->find($id);
        $post->load(['user', 'trip', 'sharedPost', 'uploads', 'likes', 'comments']);
        return $post;
    }

    public function store(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            return $this->repository->update($post, $data);
        });
    }

    public function delete(Post $post): bool
    {
        return DB::transaction(fn() => $this->repository->delete($post));
    }

   
}
