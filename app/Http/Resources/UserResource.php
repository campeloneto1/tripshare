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
        $isAdmin = $loggedUser?->is_admin() ?? false;
        $showPrivateData = $isOwner || $isAdmin;
        $showRelationships = $this->is_public || $showPrivateData;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->when($showPrivateData, $this->phone),
            'cpf' => $this->when($showPrivateData, $this->cpf),
            'email' => $this->when($showPrivateData, $this->email),
            'email_verified_at' => $this->when($showPrivateData, $this->email_verified_at?->format('Y-m-d H:i:s')),
            'role_id' => $this->when($isAdmin, $this->role_id),
            'role' => $this->when($isAdmin, RoleResource::make($this->whenLoaded('role'))),
            'trips' => $this->when($showRelationships, TripResource::collection($this->whenLoaded('trips'))),
            'trips_participating' => $this->when($showRelationships, TripResource::collection($this->whenLoaded('tripsParticipating'))),
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'is_public' => $this->is_public,
            'avatar' => $this->avatar_url,
            'bio' => $this->bio,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->when($showPrivateData, $this->deleted_at?->format('Y-m-d H:i:s')),
            'summary' =>  $this->summary,
            'flags' => $this->flags,
        ];
    }
}
