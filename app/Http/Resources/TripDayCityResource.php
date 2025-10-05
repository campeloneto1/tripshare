<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripDayCityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,
            'city_name' => $this->city_name,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'order' => $this->order,
            'osm_id' => $this->osm_id,
            'country_code' => $this->country_code,
            'trip_day_id' => $this->trip_day_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'events' => TripDayEventResource::collection($this->whenLoaded('events')),
        ];
    }
}
