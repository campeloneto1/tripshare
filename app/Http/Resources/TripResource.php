<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
         return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'days' => TripDayResource::collection($this->whenLoaded('days')),
            'is_public' => $this->is_public,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at,
            'flags' => [
                'is_owner' => $user->id === $this->user_id,
                'is_admin' => $this->users() ->where('user_id', $user->id)->where('role', 'admin')->exists(),
                'is_participant' => $this->users()->where('user_id', $user->id)->where('role', 'participant')->exists(),
                'is_visitant' => $this->is_public && !$this->users()->where('user_id', $user->id)->exists() && $user->id !== $this->user_id,
            ],
        ];
    }
}
