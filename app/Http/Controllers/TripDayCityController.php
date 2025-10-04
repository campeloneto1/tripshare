<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripDayCityRequest;
use App\Http\Requests\UpdateTripDayCityRequest;
use App\Http\Resources\TripDayCityResource;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Services\TripDayCityService;

class TripDayCityController extends Controller
{
    public function __construct(private TripDayCityService $service) {}

    /**
     * Lista todos os dias de uma viagem.
     */
    public function index(Trip $trip, TripDay $day)
    {
        try {
            $tripDaysCities = $this->service->listByTripDay($trip->id, $day->id);
            return TripDayCityResource::collection($tripDaysCities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um dia específico.
     */
    public function show(Trip $trip, TripDay $day, TripDayCity $city)
    {
        try {
            // opcional: garantir que o dia pertence à viagem
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            return TripDayCityResource::make($this->service->find($city->id));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastra um novo dia dentro de uma viagem.
     */
    public function store(StoreTripDayCityRequest $request, Trip $trip, TripDay $day)
    {
        try {
            $data = $request->validated();
            $data['trip_day_id'] = $day->id;

            $tripDay = $this->service->store($data);
            return response()->json([
                "message" => "Cidade cadastrada com sucesso",
                "data" => TripDayCityResource::make($tripDay)
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
    public function update(UpdateTripDayCityRequest $request, Trip $trip, TripDay $day, TripDayCity $city)
    {
        try {
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $tripDay = $this->service->update($city, $request->validated());
            return response()->json([
                "message" => "Cidade atualizada com sucesso",
                "data" => TripDayCityResource::make($tripDay)
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
    public function destroy(Trip $trip, TripDay $day, TripDayCity $city)
    {
        try {
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $this->service->delete($city);
             return response()->json([
                "message" => "Cidade excluída com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
