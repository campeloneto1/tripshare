<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'phone' => $this->phone,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role_id' => $this->role_id,
            'role' => RoleResource::make($this->whenLoaded('role')),
            'trips' => TripResource::collection($this->whenLoaded('trips')),
            'trips_participating' => TripResource::collection($this->whenLoaded('tripsParticipating')),
            'is_public' => $this->is_public,
            'avatar' => $this->getAvatar(),
            'bio' => $this->bio,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
