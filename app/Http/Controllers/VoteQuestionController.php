<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteQuestionRequest;
use App\Http\Requests\UpdateVoteQuestionRequest;
use App\Http\Resources\VoteQuestionResource;
use App\Models\VoteQuestion;
use App\Services\VoteQuestionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class VoteQuestionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private VoteQuestionService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', VoteQuestion::class);
            $questions = $this->service->list($request->all());
            return VoteQuestionResource::collection($questions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(VoteQuestion $voteQuestion)
    {
        try {
            $this->authorize('view', $voteQuestion);
            $voteQuestion->load('options.votes');
            return VoteQuestionResource::make($voteQuestion);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreVoteQuestionRequest $request)
    {
        try {
            $data = $request->validated();
            $this->authorize('create', [VoteQuestion::class, $data['votable_type'], $data['votable_id']]);
            $voteQuestion = $this->service->store($data);
            return response()->json([
                "message" => "Pergunta cadastrada com sucesso",
                "data" => VoteQuestionResource::make($voteQuestion)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateVoteQuestionRequest $request, VoteQuestion $voteQuestion)
    {
        try {
            $this->authorize('update', $voteQuestion);
            $data = $request->validated();
            $voteQuestion = $this->service->update($voteQuestion, $data);
            return response()->json([
                "message" => "Pergunta atualizada com sucesso",
                "data" => VoteQuestionResource::make($voteQuestion)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(VoteQuestion $voteQuestion)
    {
        try {
            $this->authorize('delete', $voteQuestion);
            $this->service->delete($voteQuestion);
            return response()->json([
                "message" => "Pergunta excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
