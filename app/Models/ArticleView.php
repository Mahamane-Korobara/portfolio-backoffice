<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
        'ip_hash',
        'session_id',
        'country',
        'device',
        'browser',
        'os',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Vérifie si cette IP a déjà vu l'article dans les 24h
    public static function hasViewed(int $articleId, string $ipHash): bool
    {
        return self::where('article_id', $articleId)
            ->where('ip_hash', $ipHash)
            ->where('viewed_at', '>=', now()->subHours(24))
            ->exists();
    }
}
