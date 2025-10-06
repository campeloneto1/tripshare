<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostLikeResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'post' => new PostResource($this->whenLoaded('post')),
            'post_id' => $this->post_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
