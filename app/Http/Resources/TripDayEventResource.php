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
            'name' => $this->name,
            'type' => $this->type,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'xid' => $this->xid,
            'source_api' => $this->source_api,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'order' => $this->order,
            'notes' => $this->notes,
        ];
    }
}
