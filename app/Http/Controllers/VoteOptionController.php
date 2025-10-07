<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteOptionRequest;
use App\Http\Requests\UpdateVoteOptionRequest;
use App\Http\Resources\VoteOptionResource;
use App\Models\VoteOption;
use App\Models\VoteQuestion;
use App\Services\VoteOptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class VoteOptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private VoteOptionService $service) {}

    public function index(VoteQuestion $vote, Request $request)
    {
        try {
            $this->authorize('viewAny', VoteOption::class);
            $filters = array_merge($request->all(), ['vote_question_id' => $vote->id]);
            $options = $this->service->list($filters);
            return VoteOptionResource::collection($options);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(VoteQuestion $vote, VoteOption $option)
    {
        try {
            $this->authorize('view', $option);

            // Verifica se a opção pertence à pergunta
            if ($option->vote_question_id != $vote->id) {
                return response()->json(['error' => 'Opção não pertence a esta votação'], 404);
            }

            $option->load('votes');
            return VoteOptionResource::make($option);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreVoteOptionRequest $request, VoteQuestion $vote)
    {
        try {
            $this->authorize('create', VoteOption::class);
            $data = $request->validated();
            $data['vote_question_id'] = $vote->id;
            $voteOption = $this->service->store($data);
            return response()->json([
                "message" => "Opção cadastrada com sucesso",
                "data" => VoteOptionResource::make($voteOption)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateVoteOptionRequest $request, VoteQuestion $vote, VoteOption $option)
    {
        try {
            $this->authorize('update', $option);

            // Verifica se a opção pertence à pergunta
            if ($option->vote_question_id != $vote->id) {
                return response()->json(['error' => 'Opção não pertence a esta votação'], 404);
            }

            $data = $request->validated();
            $voteOption = $this->service->update($option, $data);
            return response()->json([
                "message" => "Opção atualizada com sucesso",
                "data" => VoteOptionResource::make($voteOption)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(VoteQuestion $vote, VoteOption $option)
    {
        try {
            $this->authorize('delete', $option);

            // Verifica se a opção pertence à pergunta
            if ($option->vote_question_id != $vote->id) {
                return response()->json(['error' => 'Opção não pertence a esta votação'], 404);
            }

            $this->service->delete($option);
            return response()->json([
                "message" => "Opção excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
