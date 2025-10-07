<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteOptionRequest;
use App\Http\Requests\UpdateVoteOptionRequest;
use App\Http\Resources\VoteOptionResource;
use App\Models\VoteOption;
use App\Services\VoteOptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class VoteOptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private VoteOptionService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', VoteOption::class);
            $options = $this->service->list($request->all());
            return VoteOptionResource::collection($options);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(VoteOption $voteOption)
    {
        try {
            $this->authorize('view', $voteOption);
            $voteOption = $this->service->find($voteOption->id);
            if (!$voteOption) return response()->json(['error' => 'Opção não encontrada'], 404);
            return VoteOptionResource::make($voteOption);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreVoteOptionRequest $request)
    {
        try {
            $this->authorize('create', VoteOption::class);
            $data = $request->validated();
            $voteOption = $this->service->store($data);
            return response()->json([
                "message" => "Opção cadastrada com sucesso",
                "data" => VoteOptionResource::make($voteOption)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateVoteOptionRequest $request, VoteOption $voteOption)
    {
        try {
            $this->authorize('update', $voteOption);
            $data = $request->validated();
            $voteOption = $this->service->update($voteOption, $data);
            return response()->json([
                "message" => "Opção atualizada com sucesso",
                "data" => VoteOptionResource::make($voteOption)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(VoteOption $voteOption)
    {
        try {
            $this->authorize('delete', $voteOption);
            $this->service->delete($voteOption);
            return response()->json([
                "message" => "Opção excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
