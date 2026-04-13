<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleLike extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
        'ip_hash',
        'liked_at',
    ];

    protected $casts = [
        'liked_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Toggle like : retourne true si liké, false si retiré
    public static function toggle(int $articleId, string $ipHash, ?int $userId = null): bool
    {
        $query = self::where('article_id', $articleId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('ip_hash', $ipHash);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            Article::find($articleId)->decrement('likes_count');
            return false; // retiré
        }

        self::create([
            'article_id' => $articleId,
            'user_id'    => $userId,
            'ip_hash'    => $ipHash,
            'liked_at'   => now(),
        ]);
        Article::find($articleId)->increment('likes_count');
        return true; // ajouté
    }
}
