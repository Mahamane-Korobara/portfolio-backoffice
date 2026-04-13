<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'guest_name',
        'guest_email',
        'body',
        'status',
    ];

    // --- Scopes ---

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // --- Accessors ---

    // Nom affiché : user connecté ou invité
    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Anonyme';
    }

    public function getAuthorAvatarAttribute(): ?string
    {
        return $this->user?->avatar;
    }

    // --- Actions ---

    public function approve(): void
    {
        $this->update(['status' => 'approved']);
        // Incrémenter le compteur sur l'article
        $this->article->increment('comments_count');
    }

    public function markAsSpam(): void
    {
        $this->update(['status' => 'spam']);
    }

    // --- Relations ---

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->approved()
            ->latest();
    }
}
