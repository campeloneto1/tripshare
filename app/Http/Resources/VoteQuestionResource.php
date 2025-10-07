<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteQuestionResource extends JsonResource
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
            'type' => $this->type,
            'start_at' => $this->start_at?->toDateTimeString(),
            'end_at' => $this->end_at?->toDateTimeString(),
            'is_closed' => $this->is_closed,
            'votable' => [
                'id' => $this->votable_id,
                'type' => class_basename($this->votable_type),
            ],
            'options' => VoteOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
