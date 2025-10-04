<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
     public function __construct(private PermissionService $service) {}

    public function index(Request $request)
    {
        try {
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
            return PermissionResource::make($permission);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePermissionRequest $request)
    {
        try {
            $permission = $this->service->store($request->validated());
            return PermissionResource::make($permission);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        try {
            $permission = $this->service->update($permission, $request->validated());
            return PermissionResource::make($permission);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $this->service->delete($permission);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
}
