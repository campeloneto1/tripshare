<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissaoRequest;
use App\Http\Requests\UpdatePermissaoRequest;
use App\Http\Resources\PermissaoResource;
use App\Models\Permissao;
use App\Services\PermissaoService;

class PermissaoController extends Controller
{
     public function __construct(private PermissaoService $service) {}

    public function index()
    {
        try {
            $permissoes = $this->service->list();
            return PermissaoResource::collection($permissoes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Permissao $permissao)
    {
        try {
            return PermissaoResource::make($permissao);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePermissaoRequest $request)
    {
        try {
            $permissao = $this->service->store($request->validated());
            return PermissaoResource::make($permissao);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePermissaoRequest $request, Permissao $permissao)
    {
        try {
            $permissao = $this->service->update($permissao, $request->validated());
            return PermissaoResource::make($permissao);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Permissao $permissao)
    {
        try {
            $this->service->delete($permissao);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
}
