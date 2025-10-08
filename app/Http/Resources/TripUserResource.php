<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripUserResource extends JsonResource
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
            'trip_id' => $this->trip_id,
            'user_id', $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'role' => $this->role,
            'transport_type' => $this->transport_type,
            'transport_datetime' => $this->transport_datetime,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
