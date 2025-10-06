<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PostComment;
use App\Models\Post;
use App\Repositories\PostCommentRepository;
use App\Notifications\PostCommentNotification;

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

            $comment = $this->repository->create($data);

            // Envia notificação ao autor do post (se não for o próprio usuário)
            $post = Post::find($data['post_id']);
            if ($post && $post->user_id !== $data['user_id']) {
                $post->user->notify(new PostCommentNotification(
                    auth()->user(),
                    $post,
                    $comment
                ));
            }

            // Se for resposta a um comentário, notifica o autor do comentário pai
            if (!empty($data['parent_id'])) {
                $parent = $this->repository->find($data['parent_id']);
                if ($parent && $parent->user_id !== $data['user_id'] && $parent->user_id !== $post->user_id) {
                    $parent->user->notify(new PostCommentNotification(
                        auth()->user(),
                        $post,
                        $comment
                    ));
                }
            }

            return $comment;
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
