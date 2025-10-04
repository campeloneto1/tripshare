<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripDayRequest;
use App\Http\Requests\UpdateTripDayRequest;
use App\Http\Resources\TripDayResource;
use App\Models\TripDay;
use App\Services\TripDayService;

class TripDayController extends Controller
{
   public function __construct(private TripDayService $service) {}

    public function index()
    {
        try {
            
            $tripsDays = $this->service->list();
            return TripDayResource::collection($tripsDays);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(TripDay $tripDay)
    {
        try {
            return TripDayResource::make($tripDay);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreTripDayRequest $request)
    {
        try {
            $tripDay = $this->service->store($request->validated());
            return TripDayResource::make($tripDay);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateTripDayRequest $request, TripDay $tripDay)
    {
        try {
            $tripDay = $this->service->update($tripDay, $request->validated());
            return TripDayResource::make($tripDay);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(TripDay $trip)
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
