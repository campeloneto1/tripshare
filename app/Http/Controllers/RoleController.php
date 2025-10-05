<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\SyncPermissionsRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use AuthorizesRequests;
     public function __construct(private RoleService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny',Role::class);
            $filters = $request->only(['limit', 'search']);
            $roles = $this->service->list($filters);
            return RoleResource::collection($roles);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Role $role)
    {
        try {
            $this->authorize('view', $role);
            $role = $this->service->find($role->id);
            if (!$role) return response()->json(['error' => 'Perfil não encontrado'], 404);
            return RoleResource::make($role);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $this->authorize('create',Role::class);
            $role = $this->service->store($request->validated());
             return response()->json([
                "message" => "Perfil cadastrado com sucesso",
                "data" => RoleResource::make($role)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            $this->authorize('update',$role);
            $role = $this->service->update($role, $request->validated());
            return response()->json([
                "message" => "Perfil atualizado com sucesso",
                "data" => RoleResource::make($role)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Role $role)
    {
        try {
             $this->authorize('delete',$role);
            $this->service->delete($role);
            return response()->json([
                "message" => "Perfil excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function permissions(Role $role)
    {
        try {
            $permissions = $this->service->getPermissions($role);
            return PermissionResource::collection($permissions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncPermissions(Role $role, SyncPermissionsRequest $request)
{
    try {
        $this->authorize('viewAny',Role::class);
        $permissionIds = $request->validated();
        $this->service->syncPermissions($role, $permissionIds);
        return response()->json(['message' => 'Permissões sincronizadas com sucesso.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
   
}
