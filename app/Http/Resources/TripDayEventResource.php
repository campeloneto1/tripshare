<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripDayEventResource extends JsonResource
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
            'place_id' => $this->place_id,

            // Dados do Place (via relacionamento)
            'place' => $this->whenLoaded('place', function () {
                return [
                    'id' => $this->place->id,
                    'xid' => $this->place->xid,
                    'name' => $this->place->name,
                    'type' => $this->place->type,
                    'lat' => $this->place->lat,
                    'lon' => $this->place->lon,
                    'address' => $this->place->address,
                    'city' => $this->place->city,
                    'state' => $this->place->state,
                    'country' => $this->place->country,
                    'source_api' => $this->place->source_api,
                    'average_rating' => $this->place->averageRating(),
                    'reviews_count' => $this->place->reviewsCount(),
                ];
            }),

            // Dados específicos da visita
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'order' => $this->order,
            'notes' => $this->notes,
            'price' => $this->price,
            'currency' => $this->currency,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
