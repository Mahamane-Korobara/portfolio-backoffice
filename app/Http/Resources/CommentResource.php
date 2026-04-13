<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'body'        => $this->body,
            'author_name' => $this->author_name,
            'author_avatar' => $this->author_avatar,
            'status'      => $this->status,
            'created_at'  => $this->created_at->toISOString(),
            'replies'     => CommentResource::collection($this->whenLoaded('replies')),
            // Pour le back office uniquement
            'article'     => $this->whenLoaded('article', fn() => [
                'title' => $this->article->title,
                'slug'  => $this->article->slug,
            ]),
        ];
    }
}
