<?php

namespace App\Http\Controllers;

use App\Services\PlaceService;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function __construct(private PlaceService $places) {}

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json(['error' => 'Query parameter is required'], 400);
        }

        $results = $this->places->searchByName($query);

        return response()->json($results);
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $results = $this->places->searchNearby(
            $request->lat,
            $request->lon,
            $request->get('radius', 2000)
        );

        return response()->json($results);
    }

    public function details(string $id)
    {
        return response()->json($this->places->getPlaceDetails($id));
    }
}
