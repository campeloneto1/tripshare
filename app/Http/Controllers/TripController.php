<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TripController extends Controller
{
     use AuthorizesRequests;
   public function __construct(private TripService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny',Trip::class);
            $filters = $request->only(['limit', 'search']);
            $trips = $this->service->list($filters);
            return TripResource::collection($trips);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Trip $trip)
    {
        try {
            $this->authorize('view', $trip);
            $trip = $this->service->find($trip->id);
            if (!$trip) return response()->json(['error' => 'Viagem não encontrada'], 404);
            return TripResource::make($trip);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreTripRequest $request)
    {
        try {
            $trip = $this->service->store($request->validated());
            return response()->json([
                "message" => "Viagem cadastrada com sucesso",
                "data" => TripResource::make($trip)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        try {
            $trip = $this->service->update($trip, $request->validated());
            return response()->json([
                "message" => "Viagem atualizada com sucesso",
                "data" => TripResource::make($trip)
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Trip $trip)
    {
        try {
            $this->service->delete($trip);
            return response()->json([
                "message" => "Viagem excluída com sucesso",
                "data" => TripResource::make($trip)
            ], 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
