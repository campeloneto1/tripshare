<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUploadRequest;
use App\Http\Requests\UpdateUploadRequest;
use App\Http\Resources\UploadResource;
use App\Models\Upload;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function __construct(
        protected UploadService $uploadService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $uploadableType = $request->input('uploadable_type');
        $uploadableId = $request->input('uploadable_id');

        if ($uploadableType && $uploadableId) {
            $uploads = $this->uploadService->getByUploadable($uploadableType, $uploadableId);
        } else {
            $uploads = collect();
        }

        return response()->json([
            'data' => UploadResource::collection($uploads),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUploadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Upload único
        if ($request->hasFile('file')) {
            $upload = $this->uploadService->upload(
                $request->file('file'),
                $validated['uploadable_type'],
                $validated['uploadable_id'],
                $validated['type'] ?? 'image',
                $validated['is_main'] ?? false,
                $validated['order'] ?? 0
            );

            return response()->json([
                'message' => 'Upload realizado com sucesso.',
                'data' => new UploadResource($upload),
            ], 201);
        }

        // Upload múltiplo
        if ($request->hasFile('files')) {
            $uploads = $this->uploadService->uploadMultiple(
                $request->file('files'),
                $validated['uploadable_type'],
                $validated['uploadable_id'],
                $validated['type'] ?? 'image'
            );

            return response()->json([
                'message' => 'Uploads realizados com sucesso.',
                'data' => UploadResource::collection($uploads),
            ], 201);
        }

        return response()->json([
            'message' => 'Nenhum arquivo enviado.',
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(Upload $upload): JsonResponse
    {
        return response()->json([
            'data' => new UploadResource($upload),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUploadRequest $request, Upload $upload): JsonResponse
    {
        $validated = $request->validated();

        // Se definir como principal
        if (isset($validated['is_main']) && $validated['is_main']) {
            $upload = $this->uploadService->setAsMain($upload);
        } else {
            $upload = $this->uploadService->update($upload, $validated);
        }

        return response()->json([
            'message' => 'Upload atualizado com sucesso.',
            'data' => new UploadResource($upload),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Upload $upload): JsonResponse
    {
        $this->uploadService->delete($upload);

        return response()->json([
            'message' => 'Upload deletado com sucesso.',
        ]);
    }

    /**
     * Reordena uploads
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'upload_ids' => ['required', 'array'],
            'upload_ids.*' => ['integer', 'exists:uploads,id'],
        ]);

        $this->uploadService->reorder($request->input('upload_ids'));

        return response()->json([
            'message' => 'Uploads reordenados com sucesso.',
        ]);
    }
}
