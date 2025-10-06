<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;

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
        if ($post) {
            $post->load(['user', 'trip', 'sharedPost', 'uploads']);
        }
        return $post;
    }

    public function store(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            // Valida se está compartilhando um post que já é compartilhamento
            if (!empty($data['shared_post_id'])) {
                $sharedPost = $this->repository->find($data['shared_post_id']);
                if ($sharedPost && $sharedPost->shared_post_id) {
                    throw new \InvalidArgumentException('Não é possível compartilhar um post que já é um compartilhamento.');
                }
            }
            $data['user_id'] = Auth::id();
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
