<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\SyncPermissionsRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
use App\Models\Role;
use App\Services\RoleService;

class RoleController extends Controller
{
     public function __construct(private RoleService $service) {}

    public function index()
    {
        try {
            $roles = $this->service->list();
            return RoleResource::collection($roles);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Role $role)
    {
        try {
            return RoleResource::make($role);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $role = $this->service->store($request->validated());
            return RoleResource::make($role);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            $role = $this->service->update($role, $request->validated());
            return RoleResource::make($role);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $this->service->delete($role);
            return response()->json(null, 204);
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
        $permissionIds = $request->validated();
        $this->service->syncPermissions($role, $permissionIds);
        return response()->json(['message' => 'Permissões sincronizadas com sucesso.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
   
}
