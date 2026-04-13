<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // Contenu
            'id'            => $this->id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'excerpt'       => $this->excerpt,
            'content'       => $this->content, // JSON TipTap
            'reading_time'  => $this->reading_time,
            'cover_image'   => $this->cover_image,
            'cover_gallery' => $this->cover_gallery,
            'cover_type'    => $this->cover_type,
            'published_at'  => $this->published_at?->toISOString(),
            'status'        => $this->status,

            // SEO
            'meta' => [
                'title'         => $this->meta_title,
                'description'   => $this->meta_description,
                'canonical_url' => $this->canonical_url,
                'og_image'      => $this->cover_image,
            ],

            // JSON-LD Article Schema
            'json_ld' => [
                '@context'      => 'https://schema.org',
                '@type'         => 'Article',
                'headline'      => $this->title,
                'description'   => $this->excerpt,
                'datePublished' => $this->published_at?->toISOString(),
                'dateModified'  => $this->updated_at->toISOString(),
                'author'        => [
                    '@type' => 'Person',
                    'name'  => 'Mahamane Korobara',
                    'url'   => config('app.frontend_url'),
                ],
                'publisher' => [
                    '@type' => 'Person',
                    'name'  => 'Mahamane Korobara',
                ],
                'image' => $this->cover_image,
                'url'   => $this->canonical_url,
            ],

            // Stats
            'views_count'    => $this->views_count,
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->comments_count,

            // Relations
            'category' => $this->whenLoaded('category', fn() => $this->category ? [
                'name'  => $this->category->name,
                'slug'  => $this->category->slug,
                'color' => $this->category->color,
            ] : null),
            'tags' => $this->whenLoaded(
                'tags',
                fn() =>
                $this->tags->map(fn($t) => ['name' => $t->name, 'slug' => $t->slug])
            ),

            // Navigation dans la série
            'series' => $this->whenLoaded('series', fn() => $this->series ? [
                'title'    => $this->series->title,
                'slug'     => $this->series->slug,
                'order'    => $this->series_order,
                'previous' => $this->previousInSeries()?->only(['title', 'slug']),
                'next'     => $this->nextInSeries()?->only(['title', 'slug']),
            ] : null),
        ];
    }
}
