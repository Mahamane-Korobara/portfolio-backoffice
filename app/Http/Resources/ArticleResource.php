<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'excerpt'      => $this->excerpt,
            'cover_image'  => $this->cover_image,
            'cover_type'   => $this->cover_type,
            'reading_time' => $this->reading_time,
            'published_at' => $this->published_at?->toISOString(),
            'views_count'  => $this->views_count,
            'likes_count'  => $this->likes_count,
            'comments_count' => $this->comments_count,
            'category'     => $this->whenLoaded('category', fn() => [
                'name'  => $this->category->name,
                'slug'  => $this->category->slug,
                'color' => $this->category->color,
            ]),
            'tags'         => $this->whenLoaded(
                'tags',
                fn() =>
                $this->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug])
            ),
            'series'       => $this->whenLoaded('series', fn() => $this->series ? [
                'title' => $this->series->title,
                'slug'  => $this->series->slug,
                'order' => $this->series_order,
            ] : null),
        ];
    }
}
