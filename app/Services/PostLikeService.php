<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PostLike;
use App\Models\Post;
use App\Repositories\PostLikeRepository;
use App\Notifications\PostLikeNotification;

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

    public function store(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Verifica se já existe um like deste usuário neste post
            $existing = $this->repository->findByPostAndUser(
                $data['post_id'],
                $data['user_id']
            );

            // Se já existe, REMOVE (toggle)
            if ($existing) {
                $this->repository->delete($existing);
                return [
                    'action' => 'unliked',
                    'message' => 'Like removido com sucesso',
                    'like' => null,
                ];
            }

            // Se não existe, CRIA
            $postLike = $this->repository->create($data);

            // Envia notificação ao autor do post (se não for o próprio usuário)
            $post = Post::find($data['post_id']);
            if ($post && $post->user_id !== $data['user_id']) {
                $post->user->notify(new PostLikeNotification(
                    auth()->user(),
                    $post
                ));
            }

            return [
                'action' => 'liked',
                'message' => 'Like adicionado com sucesso',
                'like' => $postLike,
            ];
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
