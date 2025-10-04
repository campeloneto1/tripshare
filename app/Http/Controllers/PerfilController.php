<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerfilRequest;
use App\Http\Requests\UpdatePerfilRequest;
use App\Http\Resources\PerfilResource;
use App\Models\Perfil;
use App\Services\PerfilService;

class PerfilController extends Controller
{
     public function __construct(private PerfilService $service) {}

    public function index()
    {
        try {
            $perfis = $this->service->list();
            return PerfilResource::collection($perfis);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Perfil $perfil)
    {
        try {
            return PerfilResource::make($perfil);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StorePerfilRequest $request)
    {
        try {
            $perfil = $this->service->store($request->validated());
            return PerfilResource::make($perfil);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePerfilRequest $request, Perfil $perfil)
    {
        try {
            $perfil = $this->service->update($perfil, $request->validated());
            return PerfilResource::make($perfil);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Perfil $perfil)
    {
        try {
            $this->service->delete($perfil);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
}
