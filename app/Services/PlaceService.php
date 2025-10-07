<?php

namespace App\Services;

use App\Models\Place;
use App\Repositories\PlaceRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PlaceService
{
    private string $opentripmapKey;

    public function __construct(private PlaceRepository $repository)
    {
        $this->opentripmapKey = config('services.opentripmap.key');
    }

    /**
     * Cria ou retorna place existente baseado nos dados do local.
     * Retorna o place_id para ser usado no TripDayEvent.
     */
    public function createOrGetPlace(array $placeData): int
    {
        // Verifica se já existe place com esse xid
        if (empty($placeData['xid'])) {
            throw new \InvalidArgumentException('XID é obrigatório para criar um place');
        }

        $data = [
            'xid' => $placeData['xid'],
            'name' => $placeData['name'] ?? '',
            'type' => $placeData['type'] ?? null,
            'lat' => $placeData['lat'] ?? null,
            'lon' => $placeData['lon'] ?? null,
            'source_api' => $placeData['source_api'] ?? null,
            'address' => $placeData['address'] ?? null,
            'city' => $placeData['city'] ?? null,
            'state' => $placeData['state'] ?? null,
            'zip_code' => $placeData['zip_code'] ?? null,
            'country' => $placeData['country'] ?? null,
        ];

        // firstOrCreate: se já existe com esse xid, retorna; senão cria
        $place = $this->repository->firstOrCreate(
            ['xid' => $placeData['xid']],
            $data
        );

        return $place->id;
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
