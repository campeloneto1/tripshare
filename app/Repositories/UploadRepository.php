<?php

namespace App\Repositories;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class UploadRepository
{
    public function create(array $data): Upload
    {
        return Upload::create($data);
    }

    public function update(Upload $upload, array $data): Upload
    {
        $upload->update($data);
        return $upload->fresh();
    }

    public function delete(Upload $upload): bool
    {
        // Deleta o arquivo do storage
        if (Storage::exists($upload->path)) {
            Storage::delete($upload->path);
        }

        return $upload->delete();
    }

    public function find(int $id): ?Upload
    {
        return Upload::find($id);
    }

    public function all(): Collection
    {
        return Upload::all();
    }

    public function getByUploadable(string $uploadableType, int $uploadableId): Collection
    {
        return Upload::where('uploadable_type', $uploadableType)
            ->where('uploadable_id', $uploadableId)
            ->orderBy('order')
            ->get();
    }

    public function getMainUpload(string $uploadableType, int $uploadableId): ?Upload
    {
        return Upload::where('uploadable_type', $uploadableType)
            ->where('uploadable_id', $uploadableId)
            ->where('is_main', true)
            ->first();
    }

    public function setAsMain(Upload $upload): Upload
    {
        // Remove is_main de outros uploads do mesmo uploadable
        Upload::where('uploadable_type', $upload->uploadable_type)
            ->where('uploadable_id', $upload->uploadable_id)
            ->where('id', '!=', $upload->id)
            ->update(['is_main' => false]);

        $upload->update(['is_main' => true]);
        return $upload->fresh();
    }

    public function reorder(array $uploadIds): void
    {
        foreach ($uploadIds as $order => $uploadId) {
            Upload::where('id', $uploadId)->update(['order' => $order]);
        }
    }
}
