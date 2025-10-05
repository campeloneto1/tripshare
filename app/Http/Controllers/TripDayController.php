<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripDayRequest;
use App\Http\Requests\UpdateTripDayRequest;
use App\Http\Resources\TripDayResource;
use App\Models\Trip;
use App\Models\TripDay;
use App\Services\TripDayService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TripDayController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private TripDayService $service) {}

    /**
     * Lista todos os dias de uma viagem.
     */
    public function index(Trip $trip)
    {
        try {
            $this->authorize('viewAny',TripDay::class);
            $tripDays = $this->service->listByTrip($trip->id);
            return TripDayResource::collection($tripDays);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um dia específico.
     */
    public function show(Trip $trip, TripDay $day)
    {
        try {
            $this->authorize('view',$day);
            // opcional: garantir que o dia pertence à viagem
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            return TripDayResource::make($this->service->find($trip->id));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastra um novo dia dentro de uma viagem.
     */
    public function store(StoreTripDayRequest $request, Trip $trip)
    {
        try {
            $this->authorize('create',TripDay::class);
            $data = $request->validated();
            $data['trip_id'] = $trip->id;

            $tripDay = $this->service->store($data);
            return response()->json([
                "message" => "Dia de viagem cadastrado com sucesso",
                "data" => TripDayResource::make($tripDay)
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
    public function update(UpdateTripDayRequest $request, Trip $trip, TripDay $day)
    {
        try {
             $this->authorize('update',$day);
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $day = $this->service->update($day, $request->validated());
            return response()->json([
                "message" => "Dia de viagem atualizado com sucesso",
                "data" => TripDayResource::make($day)
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
    public function destroy(Trip $trip, TripDay $day)
    {
        try {
             $this->authorize('delete',$day);
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $this->service->delete($day);
             return response()->json([
                "message" => "Dia de viagem excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
