<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostLikeRequest;
use App\Http\Requests\UpdatePostLikeRequest;
use App\Http\Resources\PostLikeResource;
use App\Models\Post;
use App\Models\PostLike;
use App\Services\PostLikeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
     use AuthorizesRequests;
     public function __construct(private PostLikeService $service) {}

    public function index(Request $request, Post $post)
    {
        try {
            $this->authorize('viewAny',PostLike::class);
            $filters = $request->only(['limit', 'search']);
            $filters['post_id'] = $post->id;
            $likes = $this->service->list($filters);
            return PostLikeResource::collection($likes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Post $post, PostLike $like)
    {
        try {
            $this->authorize('view', $like);
            $like = $this->service->find($like->id);
            if (!$like) return response()->json(['error' => 'Like não encontrado'], 404);
            return PostLikeResource::make($like);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePostLikeRequest $request, Post $post)
    {
        try {
            $this->authorize('create',PostLike::class);

            $result = $this->service->store($request->validated());

            // Se foi removido (unliked)
            if ($result['action'] === 'unliked') {
                return response()->json([
                    "message" => $result['message'],
                    "action" => "unliked",
                    "data" => null
                ], 200);
            }

            // Se foi adicionado (liked)
            return response()->json([
                "message" => $result['message'],
                "action" => "liked",
                "data" => PostLikeResource::make($result['like'])
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePostLikeRequest $request, Post $post, PostLike $like)
    {
        try {
            $this->authorize('update',$like);
            $like = $this->service->update($like, $request->validated());
            return response()->json([
                "message" => "Like atualizado com sucesso",
                "data" => PostLikeResource::make($like)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Post $post, PostLike $like)
    {
        try {
             $this->authorize('delete',$like);
            $this->service->delete($like);
            return response()->json([
                "message" => "Like excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
