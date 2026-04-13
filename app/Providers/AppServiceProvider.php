<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement des Observers
        Article::observe(ArticleObserver::class);
        Comment::observe(CommentObserver::class);

        // Configuration des Rate Limiters (Limitation de débit)

        // Limite les commentaires à 3 par minute par IP
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Limite les likes à 10 par minute par IP
        RateLimiter::for('likes', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Protection du login admin : 5 tentatives toutes les 5 minutes
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinutes(5, 5)->by($request->ip());
        });
    }
}
