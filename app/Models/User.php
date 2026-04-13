<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'google_id',
        'notify_new_articles',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'notify_new_articles'  => 'boolean',
        'password'             => 'hashed',
    ];

    // --- Scopes ---

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeVisitors($query)
    {
        return $query->where('role', 'visitor');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('notify_new_articles', true)
            ->whereNotNull('email_verified_at');
    }

    // --- Helpers ---

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // --- Relations ---

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(ArticleLike::class);
    }

    public function newsletterSubscription()
    {
        return $this->hasOne(NewsletterSubscriber::class);
    }
}
