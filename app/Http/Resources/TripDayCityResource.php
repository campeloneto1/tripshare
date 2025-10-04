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
            'events' => TripDayEventResource::collection($this->whenLoaded('events')),
        ];
    }
}
