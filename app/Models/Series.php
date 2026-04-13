<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Series extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'is_complete',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($series) {
            $series->slug = Str::slug($series->title);
        });
    }

    public function articles()
    {
        return $this->hasMany(Article::class)->orderBy('series_order');
    }

    public function publishedArticles()
    {
        return $this->hasMany(Article::class)
            ->published()
            ->orderBy('series_order');
    }

    // Progression de la série
    public function getProgressAttribute(): array
    {
        $total     = $this->articles()->count();
        $published = $this->articles()->published()->count();
        return [
            'total'     => $total,
            'published' => $published,
            'percent'   => $total > 0 ? round(($published / $total) * 100) : 0,
        ];
    }
}
