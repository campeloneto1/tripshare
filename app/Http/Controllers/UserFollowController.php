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
     * Lista todos os dias de uma viagem.
     */
    public function index(User $user)
    {
        try {
            $tripUsers = $this->service->listByUser($user->id);
            return UserFollowResource::collection($tripUsers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um dia específico.
     */
    public function show(User $user, userFollow $userFollow)
    {
        try {
            return UserFollowResource::make($this->service->find($userFollow->id));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastra um novo dia dentro de uma viagem.
     */
    public function store(StoreUserFollowRequest $request, User $user)
    {
        try {
            $data = $request->validated();
            $data['follower_id'] = $user->id;

            $uuserFollow = $this->service->store($data);
            return response()->json([
                "message" => "Seguidor cadastrado com sucesso",
                "data" => UserFollowResource::make($uuserFollow)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza um dia da viagem.
     */
    public function update(UpdateUserFollowRequest $request, User $user, UserFollow $userFollow)
    {
        try {
            if ($userFollow->trip_id !== $user->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $userFollow = $this->service->update($userFollow, $request->validated());
            return response()->json([
                "message" => "Seguidor atualizado com sucesso",
                "data" => UserFollowResource::make($userFollow)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exclui um dia da viagem.
     */
    public function destroy(User $user, UserFollow $userFollow)
    {
        try {
          
            $this->service->delete($userFollow);
             return response()->json([
                "message" => "Usuário excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
