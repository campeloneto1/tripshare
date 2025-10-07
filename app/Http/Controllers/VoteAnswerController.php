<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteAnswerRequest;
use App\Http\Requests\UpdateVoteAnswerRequest;
use App\Http\Resources\VoteAnswerResource;
use App\Models\VoteAnswer;
use App\Models\VoteQuestion;
use App\Services\VoteAnswerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class VoteAnswerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private VoteAnswerService $service) {}

    public function index(VoteQuestion $vote, Request $request)
    {
        try {
            $this->authorize('viewAny', VoteAnswer::class);
            $filters = array_merge($request->all(), ['vote_question_id' => $vote->id]);
            $answers = $this->service->list($filters);
            return VoteAnswerResource::collection($answers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(VoteQuestion $vote, VoteAnswer $answer)
    {
        try {
            $this->authorize('view', $answer);

            // Verifica se a resposta pertence à pergunta
            if ($answer->vote_question_id != $vote->id) {
                return response()->json(['error' => 'Resposta não pertence a esta votação'], 404);
            }

            $answer->load('option', 'user');
            return VoteAnswerResource::make($answer);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreVoteAnswerRequest $request, VoteQuestion $vote)
    {
        try {
            $this->authorize('create', VoteAnswer::class);
            $data = $request->validated();
            $voteAnswer = $this->service->store($data);
            return response()->json([
                "message" => "Resposta cadastrada com sucesso",
                "data" => VoteAnswerResource::make($voteAnswer)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateVoteAnswerRequest $request, VoteQuestion $vote, VoteAnswer $answer)
    {
        try {
            $this->authorize('update', $answer);

            // Verifica se a resposta pertence à pergunta
            if ($answer->vote_question_id != $vote->id) {
                return response()->json(['error' => 'Resposta não pertence a esta votação'], 404);
            }

            $data = $request->validated();
            $voteAnswer = $this->service->update($answer, $data);
            return response()->json([
                "message" => "Resposta atualizada com sucesso",
                "data" => VoteAnswerResource::make($voteAnswer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(VoteQuestion $vote, VoteAnswer $answer)
    {
        try {
            $this->authorize('delete', $answer);

            // Verifica se a resposta pertence à pergunta
            if ($answer->vote_question_id != $vote->id) {
                return response()->json(['error' => 'Resposta não pertence a esta votação'], 404);
            }

            $this->service->delete($answer);
            return response()->json([
                "message" => "Resposta excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
