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
            'start_date' => $this->start_date?->toDateTimeString(),
            'end_date' => $this->end_date?->toDateTimeString(),
            'is_closed' => $this->is_closed,
            'closed_at' => $this->closed_at?->toDateTimeString(),
            'votable' => [
                'id' => $this->votable_id,
                'type' => class_basename($this->votable_type),
            ],
            'options' => VoteOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
