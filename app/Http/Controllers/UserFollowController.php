<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserFollowRequest;
use App\Http\Requests\UpdateUserFollowRequest;
use App\Http\Resources\UserFollowResource;
use App\Models\User;
use App\Models\UserFollow;
use App\Services\UserFollowService;

class UserFollowController extends Controller
{
     public function __construct(private UserFollowService $service) {}

    /**
     * Lista todos os seguidores/seguindo de um usuário.
     */
    public function index(User $user)
    {
        $this->authorize('viewAny', UserFollow::class);

        try {
            $follows = $this->service->listByUser($user->id);
            return UserFollowResource::collection($follows);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um relacionamento de follow específico.
     */
    public function show(User $user, UserFollow $userFollow)
    {
        $this->authorize('view', $userFollow);

        try {
            return UserFollowResource::make($this->service->find($userFollow->id));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cria um novo follow (seguir um usuário).
     */
    public function store(StoreUserFollowRequest $request, User $user)
    {
        $this->authorize('create', UserFollow::class);

        try {
            $data = $request->validated();

            // Validação: não pode seguir a si mesmo
            if ($data['following_id'] == auth()->id()) {
                return response()->json(['error' => 'Você não pode seguir a si mesmo.'], 400);
            }

            $userFollow = $this->service->store($data);
            return response()->json([
                "message" => "Follow criado com sucesso",
                "data" => UserFollowResource::make($userFollow)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza um follow (aceitar/rejeitar solicitação).
     */
    public function update(UpdateUserFollowRequest $request, User $user, UserFollow $userFollow)
    {
        $this->authorize('update', $userFollow);

        try {
            $userFollow = $this->service->update($userFollow, $request->validated());
            return response()->json([
                "message" => "Follow atualizado com sucesso",
                "data" => UserFollowResource::make($userFollow)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exclui um follow (unfollow).
     */
    public function destroy(User $user, UserFollow $userFollow)
    {
        $this->authorize('delete', $userFollow);

        try {
            $this->service->delete($userFollow);
            return response()->json([
                "message" => "Follow removido com sucesso",
                "data" => null
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
