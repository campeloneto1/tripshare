<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripDayRequest;
use App\Http\Requests\UpdateTripDayRequest;
use App\Http\Resources\TripDayResource;
use App\Models\Trip;
use App\Models\TripDay;
use App\Services\TripDayService;
use Illuminate\Http\JsonResponse;

class TripDayController extends Controller
{
    public function __construct(private TripDayService $service) {}

    /**
     * Lista todos os dias de uma viagem.
     */
    public function index(Trip $trip): JsonResponse
    {
        try {
            $tripDays = $this->service->listByTrip($trip->id);
            return response()->json(TripDayResource::collection($tripDays));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastra um novo dia dentro de uma viagem.
     */
    public function store(StoreTripDayRequest $request, Trip $trip): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['trip_id'] = $trip->id;

            $tripDay = $this->service->store($data);
            return response()->json(TripDayResource::make($tripDay), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um dia específico.
     */
    public function show(Trip $trip, TripDay $tripDay): JsonResponse
    {
        try {
            // opcional: garantir que o dia pertence à viagem
            if ($tripDay->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            return response()->json(TripDayResource::make($tripDay));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza um dia da viagem.
     */
    public function update(UpdateTripDayRequest $request, Trip $trip, TripDay $tripDay): JsonResponse
    {
        try {
            if ($tripDay->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $tripDay = $this->service->update($tripDay, $request->validated());
            return response()->json(TripDayResource::make($tripDay));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exclui um dia da viagem.
     */
    public function destroy(Trip $trip, TripDay $tripDay): JsonResponse
    {
        try {
            if ($tripDay->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $this->service->delete($tripDay);
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
