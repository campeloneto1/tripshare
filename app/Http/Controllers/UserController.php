<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private UserService $service) {}

    public function index(Request $request)
    {
        try {
             $this->authorize('viewAny',User::class);
            $filters = $request->only(['limit', 'search']);
            $users = $this->service->list($filters);
            return UserResource::collection($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function indexAll(Request $request)
    {
        try {
             $this->authorize('viewAny',User::class);
            $filters = $request->only(['limit', 'search']);
            $users = $this->service->listAll($filters);
            return UserResource::collection($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(User $user)
    {
        try {
             $this->authorize('view',$user);
            $user = $this->service->find($user->id);
            if (!$user) return response()->json(['error' => 'Usuário não encontrado'], 404);
            return UserResource::make($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showWithTrashed(int $id)
    {
        try {
            
            $user = $this->service->findWithTrashed($id);
            if (!$user) return response()->json(['error' => 'Usuário não encontrado'], 404);
            return UserResource::make($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
             $this->authorize('create',User::class);
            $user = $this->service->store($request->validated());
            return response()->json([
                "message" => "Usuário cadastrado com sucesso",
                "data" => UserResource::make($user)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $this->authorize('update',$user);
            $user = $this->service->update($user, $request->validated());
            return response()->json([
                "message" => "Usuário atualizado com sucesso",
                "data" => UserResource::make($user)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $this->authorize('delete',$user);
            $this->service->delete($user);
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

    public function restore(int $id)
    {
        try {
            
            $user = $this->service->findWithTrashed($id);
            if (!$user) return response()->json(['error' => 'Usuário não encontrado'], 404);

            $this->service->restore($user);
            return UserResource::make($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id)
    {
        try {
            $user = $this->service->findWithTrashed($id);
            if (!$user) return response()->json(['error' => 'Usuário não encontrado'], 404);

            $this->service->forceDelete($user);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchUsers(Request $request){
        try {
            $this->authorize('viewAny',User::class);
            $filters = $request->only(['limit', 'search']);
            $users = $this->service->searchUsers($filters);
            return UserResource::collection($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
