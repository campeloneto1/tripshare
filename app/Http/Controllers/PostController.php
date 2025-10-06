<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use AuthorizesRequests;
     public function __construct(private PostService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny',Post::class);
            $filters = $request->only(['limit', 'search']);
            $posts = $this->service->list($filters);
            return PostResource::collection($posts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Post $post)
    {
        try {
            $this->authorize('view', $post);
            $post = $this->service->find($post->id);
            if (!$post) return response()->json(['error' => 'Post não encontrado'], 404);
            return PostResource::make($post);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePostRequest $request)
    {
        try {
            $this->authorize('create',Post::class);
            $post = $this->service->store($request->validated());
             return response()->json([
                "message" => "Post cadastrado com sucesso",
                "data" => PostResource::make($post)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        try {
            $this->authorize('update',$post);
            $post = $this->service->update($post, $request->validated());
            return response()->json([
                "message" => "Post atualizado com sucesso",
                "data" => PostResource::make($post)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Post $post)
    {
        try {
             $this->authorize('delete',$post);
            $this->service->delete($post);
            return response()->json([
                "message" => "Post excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
