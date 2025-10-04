<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripUserRequest;
use App\Http\Requests\UpdateTripUserRequest;
use App\Models\TripUser;

class TripUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTripUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TripUser $tripUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTripUserRequest $request, TripUser $tripUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TripUser $tripUser)
    {
        //
    }
}
