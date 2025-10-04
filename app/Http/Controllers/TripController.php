<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Services\TripService;

class TripController extends Controller
{
   public function __construct(private TripService $service) {}

    public function index()
    {
        try {
            
            $trips = $this->service->list();
            return TripResource::collection($trips);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Trip $trip)
    {
        try {
            return TripResource::make($trip);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreTripRequest $request)
    {
        try {
            $trip = $this->service->store($request->validated());
            return TripResource::make($trip);
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
            return TripResource::make($trip);
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
            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
