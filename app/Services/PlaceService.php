<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PlaceService
{
    private string $opentripmapKey;

    public function __construct()
    {
        $this->opentripmapKey = config('services.opentripmap.key');
    }

    /**
     * Busca local por nome (usando Nominatim)
     */
    public function searchByName(string $query, int $limit = 5)
    {
        return Cache::remember("places:nominatim:{$query}", now()->addHours(6), function () use ($query, $limit) {
            $response = Http::withHeaders([
                'User-Agent' => 'TripShareApp/1.0 (campeloneto1@gmail.com)', // coloque um email seu real aqui
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => $limit,
            ]);

            return $response->json();
        });
    }

    /**
     * Busca pontos turísticos próximos (usando OpenTripMap)
     */
    public function searchNearby(float $lat, float $lon, int $radius = 2000)
    {
        return Cache::remember("places:opentripmap:{$lat}:{$lon}:{$radius}", now()->addHours(6), function () use ($lat, $lon, $radius) {
            $response = Http::get('https://api.opentripmap.com/0.1/en/places/radius', [
                'radius' => $radius,
                'lon' => $lon,
                'lat' => $lat,
                'apikey' => $this->opentripmapKey,
            ]);

            return $response->json();
        });
    }

    /**
     * Detalha um ponto turístico específico (OpenTripMap)
     */
    public function getPlaceDetails(string $xid)
    {
        return Cache::remember("places:opentripmap:xid:{$xid}", now()->addDays(1), function () use ($xid) {
            $response = Http::get("https://api.opentripmap.com/0.1/en/places/xid/{$xid}", [
                'apikey' => $this->opentripmapKey,
            ]);

            return $response->json();
        });
    }
}
