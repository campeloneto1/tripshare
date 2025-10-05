<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripUserRequest;
use App\Http\Requests\UpdateTripUserRequest;
use App\Http\Resources\TripUserResource;
use App\Models\Trip;
use App\Models\TripUser;
use App\Services\TripUserService;

class TripUserController extends Controller
{
    public function __construct(private TripUserService $service) {}

    /**
     * Lista todos os dias de uma viagem.
     */
    public function index(Trip $trip)
    {
        try {
            $tripUsers = $this->service->listByTrip($trip->id);
            return TripUserResource::collection($tripUsers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um dia específico.
     */
    public function show(Trip $trip, TripUser $tripUser)
    {
        try {
            // opcional: garantir que o dia pertence à viagem
            if ($tripUser->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            return TripUserResource::make($this->service->find($trip->id));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastra um novo dia dentro de uma viagem.
     */
    public function store(StoreTripUserRequest $request, Trip $trip)
    {
        try {
            $data = $request->validated();
            $data['trip_id'] = $trip->id;

            $tripUser = $this->service->store($data);
            return response()->json([
                "message" => "Usuário cadastrado com sucesso",
                "data" => TripUserResource::make($tripUser)
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
    public function update(UpdateTripUserRequest $request, Trip $trip, TripUser $tripUser)
    {
        try {
            if ($tripUser->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $tripUser = $this->service->update($tripUser, $request->validated());
            return response()->json([
                "message" => "Usuário atualizado com sucesso",
                "data" => TripUserResource::make($tripUser)
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
    public function destroy(Trip $trip, TripUser $tripUser)
    {
        try {
            if ($tripUser->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $this->service->delete($tripUser);
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
