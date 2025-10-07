<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteOptionResource extends JsonResource
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
            'title' => $this->title,
            'json_data' => $this->json_data,
            'votes_count' => $this->whenCounted('votes') ?? $this->votes()->count(),
            'votes' => VoteAnswerResource::collection($this->whenLoaded('votes')),
        ];
    }
}
