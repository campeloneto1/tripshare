<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'content' => $this->content,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            // Autor do post
            'user' => UserResource::make($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            // Viagem vinculada (se houver)
            'trip' => TripResource::make($this->whenLoaded('trip')),
            'trip_id' => $this->trip_id,
            // Post compartilhado (se houver)
            'shared_post' => PostResource::make($this->whenLoaded('sharedPost')),
            'shared_post_id' => $this->shared_post_id,
            // Uploads (fotos / vídeos)
            'uploads' => UploadResource::collection($this->whenLoaded('uploads')),
            'likes' => PostLikeResource::collection($this->whenLoaded('likes')),
            'comments' => PostCommentResource::collection($this->whenLoaded('comments')),

            'type' => $this->type(),
            'summary' => $this->summary(),
            'flags' => $this->flags(),
        ];
    }
}
