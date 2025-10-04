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
