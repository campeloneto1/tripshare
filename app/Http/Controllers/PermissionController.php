<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private PermissionService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny',Permission::class);
            $filters = $request->only(['limit', 'search']);
            $permissions = $this->service->list($filters);
            return PermissionResource::collection($permissions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Permission $permission)
    {
        try {
            $this->authorize('view', $permission);
            $permission = $this->service->find($permission->id);
            if (!$permission) return response()->json(['error' => 'Permissão não encontrada'], 404);
            return PermissionResource::make($permission);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePermissionRequest $request)
    {
        try {
            $this->authorize('create',Permission::class);
            $permission = $this->service->store($request->validated());
            return response()->json([
                "message" => "Permissão cadastrada com sucesso",
                "data" => PermissionResource::make($permission)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        try {
            $this->authorize('update',$permission);
            $permission = $this->service->update($permission, $request->validated());
             return response()->json([
                "message" => "Permissão atualizada com sucesso",
                "data" => PermissionResource::make($permission)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $this->authorize('delete',$permission);
            $this->service->delete($permission);
             return response()->json([
                "message" => "Permissão excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
}
