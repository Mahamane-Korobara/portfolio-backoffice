<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'excerpt'          => $this->excerpt,
            'content'          => $this->content,
            'reading_time'     => $this->reading_time,
            'cover_image'      => $this->cover_image,
            'cover_gallery'    => $this->cover_gallery,
            'cover_type'       => $this->cover_type,
            'status'           => $this->status,
            'status_label'     => $this->status_label,
            'published_at'     => $this->published_at?->toISOString(),
            'scheduled_at'     => $this->scheduled_at?->toISOString(),
            'preview_token'    => $this->preview_token,
            'preview_url'      => $this->preview_url,
            'series_order'     => $this->series_order,
            'views_count'      => $this->views_count,
            'likes_count'      => $this->likes_count,
            'comments_count'   => $this->comments_count,
            'meta_title'       => $this->getRawOriginal('meta_title'),
            'meta_description' => $this->getRawOriginal('meta_description'),
            'canonical_url'    => $this->getRawOriginal('canonical_url'),
            'category_id'      => $this->category_id,
            'series_id'        => $this->series_id,
            'deleted_at'       => $this->deleted_at?->toISOString(),
            'created_at'       => $this->created_at->toISOString(),
            'updated_at'       => $this->updated_at->toISOString(),
            'category'         => $this->whenLoaded('category', fn() => $this->category ? [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'slug'  => $this->category->slug,
                'color' => $this->category->color,
            ] : null),
            'tags'             => $this->whenLoaded(
                'tags',
                fn() =>
                $this->tags->map(fn($t) => [
                    'id'   => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                ])
            ),
            'series'           => $this->whenLoaded('series', fn() => $this->series ? [
                'id'    => $this->series->id,
                'title' => $this->series->title,
                'slug'  => $this->series->slug,
            ] : null),
        ];
    }
}
