<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PostLike;
use App\Repositories\PostLikeRepository;

class PostLikeService
{
    public function __construct(private PostLikeRepository $repository) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    public function find(int $id): ?PostLike
    {
        $post = $this->repository->find($id);
        return $post;
    }

    public function store(array $data): PostLike
    {
        return DB::transaction(function () use ($data) {
            // Verifica se já existe um like deste usuário neste post
            $existing = $this->repository->findByPostAndUser(
                $data['post_id'],
                $data['user_id']
            );

            if ($existing) {
                throw new \InvalidArgumentException('Você já curtiu este post.');
            }

            return $this->repository->create($data);
        });
    }

    public function update(PostLike $postLike, array $data): PostLike
    {
        return DB::transaction(function () use ($postLike, $data) {
            return $this->repository->update($postLike, $data);
        });
    }

    public function delete(PostLike $postLike): bool
    {
        return DB::transaction(fn() => $this->repository->delete($postLike));
    }

   
}
