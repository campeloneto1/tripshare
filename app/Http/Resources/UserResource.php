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
        $loggedUser = $request->user();
        $isOwner = $loggedUser && $loggedUser->id === $this->id;
        $showRelationships = $this->is_public || $isOwner;
        if($loggedUser){
            $is_admin = $loggedUser->is_admin();
        } else {
            $is_admin = false;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role_id' => $this->role_id,
            'role' => $is_admin ? RoleResource::make($this->whenLoaded('role')) : null,
            'trips' => $showRelationships ? TripResource::collection($this->whenLoaded('trips')) : null,
            'trips_participating' => $showRelationships ? TripResource::collection($this->whenLoaded('tripsParticipating')) : null,
            'is_public' => $this->is_public,
            'avatar' => $this->getAvatar(),
            'bio' => $this->bio,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at,
            'summary' =>  $this->summary(),
            'flags' => $this->flags(),
        ];
    }
}
