<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostCommentRequest;
use App\Http\Requests\UpdatePostCommentRequest;
use App\Http\Resources\PostCommentResource;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\PostCommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    use AuthorizesRequests;
     public function __construct(private PostCommentService $service) {}

    public function index(Request $request, Post $post)
    {
        try {
            $this->authorize('viewAny',PostComment::class);
            $filters = $request->only(['limit', 'search']);
            $posts = $this->service->list($filters);
            return PostCommentResource::collection($posts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Post $post, PostComment $comment)
    {
        try {
            $this->authorize('view', $comment);
            $comment = $this->service->find($comment->id);
            if (!$comment) return response()->json(['error' => 'Perfil não encontrado'], 404);
            return PostCommentResource::make($comment);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePostCommentRequest $request, Post $post)
    {
        try {
            $this->authorize('create',PostComment::class);
            $postComment = $this->service->store($request->validated());
             return response()->json([
                "message" => "Comment cadastrado com sucesso",
                "data" => PostCommentResource::make($postComment)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePostCommentRequest $request, Post $post, PostComment $comment)
    {
        try {
            $this->authorize('update',$comment);
            $comment = $this->service->update($comment, $request->validated());
            return response()->json([
                "message" => "Comment atualizado com sucesso",
                "data" => PostCommentResource::make($comment)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Post $post, PostComment $comment)
    {
        try {
             $this->authorize('delete',$comment);
            $this->service->delete($comment);
            return response()->json([
                "message" => "Comment excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
