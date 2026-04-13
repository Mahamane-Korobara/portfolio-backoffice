<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'series_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'reading_time',
        'cover_image',
        'cover_gallery',
        'cover_type',
        'meta_title',
        'meta_description',
        'canonical_url',
        'status',
        'published_at',
        'scheduled_at',
        'preview_token',
        'series_order',
        'views_count',
        'likes_count',
        'comments_count',
    ];

    protected $casts = [
        'content'       => 'array',   // JSON TipTap
        'cover_gallery' => 'array',
        'published_at'  => 'datetime',
        'scheduled_at'  => 'datetime',
        'reading_time'  => 'integer',
        'series_order'  => 'integer',
    ];

    // --- Boot : auto slug + preview token + reading time ---

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            if (empty($article->preview_token)) {
                $article->preview_token = Str::random(64);
            }
            $article->reading_time = self::calculateReadingTime($article->content);
        });

        static::updating(function ($article) {
            // Recalcul du temps de lecture si le contenu change
            if ($article->isDirty('content')) {
                $article->reading_time = self::calculateReadingTime($article->content);
            }

            // Gestion automatique de la date de publication lors du changement de statut
            if ($article->isDirty('status') && $article->status === 'published') {
                $article->published_at = $article->published_at ?? now();
            }
        });
    }

    // --- Calcul temps de lecture (Logique TipTap) ---

    public static function calculateReadingTime(?array $content): int
    {
        if (!$content) return 0;
        $text = self::extractTextFromTipTap($content);
        $wordCount = str_word_count(strip_tags($text));

        // Base de 200 mots/minute, minimum 1 minute
        return (int) max(1, ceil($wordCount / 200));
    }

    private static function extractTextFromTipTap(array $content): string
    {
        $text = '';
        if (isset($content['content']) && is_array($content['content'])) {
            foreach ($content['content'] as $node) {
                if (isset($node['text'])) {
                    $text .= $node['text'] . ' ';
                }
                if (isset($node['content']) && is_array($node['content'])) {
                    $text .= self::extractTextFromTipTap($node);
                }
            }
        }
        return $text;
    }

    // --- Scopes ---

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '>', now());
    }

    // --- Accessors (SEO & UI) ---

    public function getMetaTitleAttribute($value): string
    {
        return $value ?? ($this->title . ' — sahelstack.tech');
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?? Str::limit($this->excerpt, 160);
    }

    public function getCanonicalUrlAttribute($value): string
    {
        return $value ?? (config('app.frontend_url') . '/blog/' . $this->slug);
    }

    public function getPreviewUrlAttribute(): string
    {
        return config('app.frontend_url') . '/blog/preview/' . $this->preview_token;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Publié',
            'draft'     => 'Brouillon',
            'scheduled' => 'Programmé',
            default     => 'Inconnu',
        };
    }

    // --- Actions métier ---

    public function unpublish(): void
    {
        $this->update([
            'status'       => 'draft',
            'published_at' => null,
        ]);
    }

    public function publish(): void
    {
        $this->update([
            'status'       => 'published',
            'published_at' => now(),
            'scheduled_at' => null,
        ]);
    }

    public function schedule(Carbon $date): void
    {
        $this->update([
            'status'       => 'scheduled',
            'scheduled_at' => $date,
        ]);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    // --- Relations ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with('replies')
            ->latest();
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ArticleLike::class);
    }

    // Navigation au sein d'une série
    public function previousInSeries(): ?Article
    {
        if (!$this->series_id || $this->series_order === null) return null;
        return self::where('series_id', $this->series_id)
            ->where('series_order', '<', $this->series_order)
            ->orderBy('series_order', 'desc')
            ->first();
    }

    public function nextInSeries(): ?Article
    {
        if (!$this->series_id || $this->series_order === null) return null;
        return self::where('series_id', $this->series_id)
            ->where('series_order', '>', $this->series_order)
            ->orderBy('series_order', 'asc')
            ->first();
    }
}
