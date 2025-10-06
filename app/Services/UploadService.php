<?php

namespace App\Services;

use App\Models\Upload;
use App\Repositories\UploadRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    public function __construct(
        protected UploadRepository $uploadRepository
    ) {}

    /**
     * Faz upload de um arquivo e cria o registro no banco
     */
    public function upload(
        UploadedFile $file,
        string $uploadableType,
        int $uploadableId,
        string $type = 'image',
        bool $isMain = false,
        int $order = 0
    ): Upload {
        // Gera um nome único para o arquivo
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Define o caminho baseado no tipo
        $path = "{$type}s/{$filename}";

        // Armazena o arquivo
        Storage::disk('public')->put($path, file_get_contents($file));

        // Cria o registro no banco
        return $this->uploadRepository->create([
            'uploadable_type' => $uploadableType,
            'uploadable_id' => $uploadableId,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'type' => $type,
            'size' => $file->getSize(),
            'order' => $order,
            'is_main' => $isMain,
        ]);
    }

    /**
     * Faz upload de múltiplos arquivos
     */
    public function uploadMultiple(
        array $files,
        string $uploadableType,
        int $uploadableId,
        string $type = 'image'
    ): Collection {
        $uploads = collect();

        foreach ($files as $index => $file) {
            $uploads->push($this->upload(
                $file,
                $uploadableType,
                $uploadableId,
                $type,
                $index === 0, // Primeiro arquivo é o principal
                $index
            ));
        }

        return $uploads;
    }

    /**
     * Atualiza um upload
     */
    public function update(Upload $upload, array $data): Upload
    {
        return $this->uploadRepository->update($upload, $data);
    }

    /**
     * Deleta um upload e o arquivo físico
     */
    public function delete(Upload $upload): bool
    {
        return $this->uploadRepository->delete($upload);
    }

    /**
     * Define um upload como principal
     */
    public function setAsMain(Upload $upload): Upload
    {
        return $this->uploadRepository->setAsMain($upload);
    }

    /**
     * Reordena uploads
     */
    public function reorder(array $uploadIds): void
    {
        $this->uploadRepository->reorder($uploadIds);
    }

    /**
     * Busca uploads de um uploadable
     */
    public function getByUploadable(string $uploadableType, int $uploadableId): Collection
    {
        return $this->uploadRepository->getByUploadable($uploadableType, $uploadableId);
    }

    /**
     * Busca o upload principal de um uploadable
     */
    public function getMainUpload(string $uploadableType, int $uploadableId): ?Upload
    {
        return $this->uploadRepository->getMainUpload($uploadableType, $uploadableId);
    }

    /**
     * Substitui todos os uploads de um uploadable
     */
    public function replaceAll(
        array $files,
        string $uploadableType,
        int $uploadableId,
        string $type = 'image'
    ): Collection {
        // Deleta uploads existentes
        $existingUploads = $this->getByUploadable($uploadableType, $uploadableId);
        foreach ($existingUploads as $upload) {
            $this->delete($upload);
        }

        // Faz upload dos novos arquivos
        return $this->uploadMultiple($files, $uploadableType, $uploadableId, $type);
    }
}
