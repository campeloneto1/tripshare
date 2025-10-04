<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreViagemRequest;
use App\Http\Requests\UpdateViagemRequest;
use App\Http\Resources\ViagemResource;
use App\Models\Viagem;
use App\Services\ViagemService;

class ViagemController extends Controller
{
   public function __construct(private ViagemService $service) {}

    public function index()
    {
        try {
            
            $viagens = $this->service->list();
            return ViagemResource::collection($viagens);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Viagem $viagem)
    {
        try {
            return ViagemResource::make($viagem);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreViagemRequest $request)
    {
        try {
            $viagem = $this->service->store($request->validated());
            return ViagemResource::make($viagem);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateViagemRequest $request, Viagem $viagem)
    {
        try {
            $viagem = $this->service->update($viagem, $request->validated());
            return ViagemResource::make($viagem);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Viagem $viagem)
    {
        try {
            $this->service->delete($viagem);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
