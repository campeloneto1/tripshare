<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFollowResource extends JsonResource
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
            'status' => $this->status,
            'accepted_at' => $this->accepted_at ? $this->accepted_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            // Usuário que segue
            'follower' => [
                'id' => $this->follower->id,
                'name' => $this->follower->name,
                'avatar' => $this->follower->avatar ?? null,
            ],

            // Usuário seguido
            'following' => [
                'id' => $this->following->id,
                'name' => $this->following->name,
                'avatar' => $this->following->avatar ?? null,
            ],
        ];
    }
}
