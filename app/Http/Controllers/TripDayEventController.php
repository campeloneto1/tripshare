<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripDayEventRequest;
use App\Http\Requests\UpdateTripDayEventRequest;
use App\Http\Resources\TripDayEventResource;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripDayCity;
use App\Models\TripDayEvent;
use App\Services\TripDayEventService;

class TripDayEventController extends Controller
{
    public function __construct(private TripDayEventService $service) {}

    /**
     * Lista todos os dias de uma viagem.
     */
    public function index(Trip $trip, TripDay $day, TripDayCity $city)
    {
        try {
            $tripDaysEvents = $this->service->listByTripDayCity($trip->id, $day->id, $city->id);
            return TripDayEventResource::collection($tripDaysEvents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra um dia específico.
     */
    public function show(Trip $trip, TripDay $day, TripDayCity $city, TripDayEvent $event)
    {
        try {
            // opcional: garantir que o dia pertence à viagem
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            return TripDayEventResource::make($this->service->find($event->id));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastra um novo dia dentro de uma viagem.
     */
    public function store(StoreTripDayEventRequest $request, Trip $trip, TripDay $day, TripDayCity $city)
    {
        try {
            $data = $request->validated();
            $data['trip_day_city_id'] = $city->id;

            $tripDayEvent = $this->service->store($data);
            return response()->json([
                "message" => "Evento cadastrado com sucesso",
                "data" => TripDayEventResource::make($tripDayEvent)
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
    public function update(UpdateTripDayEventRequest $request, Trip $trip, TripDay $day, TripDayCity $city, TripDayEvent $event)
    {
        try {
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $tripDay = $this->service->update($event, $request->validated());
            return response()->json([
                "message" => "Evento atualizado com sucesso",
                "data" => TripDayEventResource::make($tripDay)
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
    public function destroy(Trip $trip, TripDay $day, TripDayCity $city, TripDayEvent $event)
    {
        try {
            if ($day->trip_id !== $trip->id) {
                return response()->json(['error' => 'Trip day does not belong to this trip.'], 403);
            }

            $this->service->delete($event);
             return response()->json([
                "message" => "Evento excluído com sucesso",
                "data" => null
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
