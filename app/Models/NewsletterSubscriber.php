<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'name',
        'token',
        'confirmed',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'confirmed'        => 'boolean',
        'confirmed_at'     => 'datetime',
        'unsubscribed_at'  => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($sub) {
            $sub->token = Str::random(32);
        });
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('confirmed', true)
            ->whereNull('unsubscribed_at');
    }

    // --- Actions ---

    public function confirm(): void
    {
        $this->update([
            'confirmed'    => true,
            'confirmed_at' => now(),
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update(['unsubscribed_at' => now()]);
    }

    // Lien de désabonnement
    public function getUnsubscribeUrlAttribute(): string
    {
        return config('app.url') . '/newsletter/unsubscribe/' . $this->token;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
