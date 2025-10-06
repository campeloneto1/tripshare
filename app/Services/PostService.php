<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Repositories\PostRepository;
use App\Services\UploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PostService
{
    public function __construct(
        private PostRepository $repository,
        private UploadService $uploadService
    ) {}

    public function list(array $filters)
    {
        return $this->repository->all($filters);
    }

    /**
     * Retorna o feed do usuário (com cache)
     */
    public function getFeed(int $userId, int $limit = 20)
    {
        return $this->repository->getFeedForUser($userId, $limit);
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

            // Extrai uploads antes de criar o post
            $uploadFiles = $data['uploads'] ?? [];
            unset($data['uploads']);

            // Cria o post
            $post = $this->repository->create($data);

            // Processa uploads se existirem
            if (!empty($uploadFiles)) {
                foreach ($uploadFiles as $index => $file) {
                    $type = $this->detectFileType($file);
                    $this->uploadService->upload(
                        $file,
                        Post::class,
                        $post->id,
                        $type,
                        $index === 0, // Primeiro é o principal
                        $index
                    );
                }
            }

            // Invalida cache do feed do usuário
            $this->clearUserFeedCache(Auth::id());

            return $post->load('uploads');
        });
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            // Extrai uploads e uploads_removed
            $uploadFiles = $data['uploads'] ?? [];
            $uploadsRemoved = $data['uploads_removed'] ?? [];
            unset($data['uploads'], $data['uploads_removed']);

            // Atualiza o post
            $post = $this->repository->update($post, $data);

            // Remove uploads marcados
            if (!empty($uploadsRemoved)) {
                foreach ($uploadsRemoved as $uploadId) {
                    $upload = $post->uploads()->find($uploadId);
                    if ($upload) {
                        $this->uploadService->delete($upload);
                    }
                }
            }

            // Adiciona novos uploads
            if (!empty($uploadFiles)) {
                $currentMaxOrder = $post->uploads()->max('order') ?? -1;
                foreach ($uploadFiles as $index => $file) {
                    $type = $this->detectFileType($file);
                    $this->uploadService->upload(
                        $file,
                        Post::class,
                        $post->id,
                        $type,
                        false,
                        $currentMaxOrder + $index + 1
                    );
                }
            }

            // Invalida cache do feed do usuário
            $this->clearUserFeedCache($post->user_id);

            return $post->load('uploads');
        });
    }

    public function delete(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            $userId = $post->user_id;

            // Deleta uploads associados
            foreach ($post->uploads as $upload) {
                $this->uploadService->delete($upload);
            }

            $result = $this->repository->delete($post);

            // Invalida cache do feed do usuário
            $this->clearUserFeedCache($userId);

            return $result;
        });
    }

    /**
     * Invalida o cache do feed do usuário
     */
    private function clearUserFeedCache(int $userId): void
    {
        // Limpa diferentes variações de limite
        for ($limit = 10; $limit <= 50; $limit += 10) {
            Cache::forget("user_feed_{$userId}_limit_{$limit}");
        }
    }

    /**
     * Detecta o tipo do arquivo (image ou video)
     */
    private function detectFileType($file): string
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'document';
    }
}
