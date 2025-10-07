<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteAnswerRequest;
use App\Http\Requests\UpdateVoteAnswerRequest;
use App\Http\Resources\VoteAnswerResource;
use App\Models\VoteAnswer;
use App\Services\VoteAnswerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class VoteAnswerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private VoteAnswerService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', VoteAnswer::class);
            $answers = $this->service->list($request->all());
            return VoteAnswerResource::collection($answers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(VoteAnswer $voteAnswer)
    {
        try {
            $this->authorize('view', $voteAnswer);
            $voteAnswer = $this->service->find($voteAnswer->id);
            if (!$voteAnswer) return response()->json(['error' => 'Resposta não encontrada'], 404);
            return VoteAnswerResource::make($voteAnswer);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreVoteAnswerRequest $request)
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

    public function update(UpdateVoteAnswerRequest $request, VoteAnswer $voteAnswer)
    {
        try {
            $this->authorize('update', $voteAnswer);
            $data = $request->validated();
            $voteAnswer = $this->service->update($voteAnswer, $data);
            return response()->json([
                "message" => "Resposta atualizada com sucesso",
                "data" => VoteAnswerResource::make($voteAnswer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(VoteAnswer $voteAnswer)
    {
        try {
            $this->authorize('delete', $voteAnswer);
            $this->service->delete($voteAnswer);
            return response()->json([
                "message" => "Resposta excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
