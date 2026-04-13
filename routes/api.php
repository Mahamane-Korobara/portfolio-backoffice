<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Public;
use App\Http\Controllers\Api\Admin;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES — accessibles par tout le monde (Next.js frontend)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Articles
    Route::get('/articles',                [Public\ArticleController::class, 'index']);
    Route::get('/articles/{slug}',         [Public\ArticleController::class, 'show']);
    Route::get('/articles/{slug}/related', [Public\ArticleController::class, 'related']);
    Route::post('/articles/{slug}/view',   [Public\ArticleController::class, 'trackView']);
    Route::post('/articles/{slug}/like',   [Public\ArticleController::class, 'toggleLike'])->middleware('throttle:likes');

    // Prévisualisation brouillon (lien secret)
    Route::get('/preview/{token}', [Public\ArticleController::class, 'preview']);

    // Commentaires
    Route::get('/articles/{slug}/comments',  [Public\CommentController::class, 'index']);
    Route::post('/articles/{slug}/comments', [Public\CommentController::class, 'store'])->middleware('throttle:comments');

    // Catégories & Tags
    Route::get('/categories', [Public\CategoryController::class, 'index']);
    Route::get('/tags',        [Public\TagController::class, 'index']);

    // Séries
    Route::get('/series',       [Public\SeriesController::class, 'index']);
    Route::get('/series/{slug}', [Public\SeriesController::class, 'show']);

    // Newsletter
    Route::post('/newsletter/subscribe',          [Public\NewsletterController::class, 'subscribe']);
    Route::get('/newsletter/confirm/{token}',     [Public\NewsletterController::class, 'confirm']);
    Route::get('/newsletter/unsubscribe/{token}', [Public\NewsletterController::class, 'unsubscribe']);

    // SEO
    Route::get('/sitemap', [Public\SeoController::class, 'sitemap']);
    Route::get('/feed',    [Public\SeoController::class, 'rss']);

    // Auth visiteurs (Google OAuth)
    Route::post('/auth/google',  [Public\AuthController::class, 'googleCallback']);
    Route::post('/auth/logout',  [Public\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/auth/me',       [Public\AuthController::class, 'me'])->middleware('auth:sanctum');

    /*
    |--------------------------------------------------------------------------
    | ROUTES PRIVÉES ADMIN — dashboard.sahelstack.tech
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->domain(config('app.dashboard_domain'))
        ->group(function () {

            // Auth admin
            Route::post('/login',  [Admin\AuthController::class, 'login'])->middleware('throttle:admin-login');
            Route::post('/logout', [Admin\AuthController::class, 'logout'])->middleware('auth:sanctum');

            // Toutes les routes suivantes nécessitent d'être admin
            Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

                // Dashboard stats
                Route::get('/stats',          [Admin\DashboardController::class, 'stats']);
                Route::get('/stats/sources',  [Admin\DashboardController::class, 'sources']);
                Route::get('/stats/devices',  [Admin\DashboardController::class, 'devices']);
                Route::get('/stats/timeline', [Admin\DashboardController::class, 'timeline']);

                // Articles
                Route::apiResource('/articles', Admin\ArticleController::class);
                Route::post('/articles/{id}/publish',   [Admin\ArticleController::class, 'publish']);
                Route::post('/articles/{id}/unpublish', [Admin\ArticleController::class, 'unpublish']);
                Route::post('/articles/{id}/schedule',  [Admin\ArticleController::class, 'schedule']);

                // Commentaires
                Route::get('/comments',                          [Admin\CommentController::class, 'index']);
                Route::post('/comments/{id}/approve',            [Admin\CommentController::class, 'approve']);
                Route::post('/comments/{id}/spam',               [Admin\CommentController::class, 'spam']);
                Route::delete('/comments/{id}',                  [Admin\CommentController::class, 'destroy']);

                // Médias
                Route::get('/media',           [Admin\MediaController::class, 'index']);
                Route::post('/media',          [Admin\MediaController::class, 'store']);
                Route::delete('/media/{id}',   [Admin\MediaController::class, 'destroy']);

                // Catégories & Tags
                Route::apiResource('/categories', Admin\CategoryController::class);
                Route::apiResource('/tags',        Admin\TagController::class);

                // Séries
                Route::apiResource('/series', Admin\SeriesController::class);

                // Abonnés
                Route::get('/subscribers',         [Admin\SubscriberController::class, 'index']);
                Route::delete('/subscribers/{id}', [Admin\SubscriberController::class, 'destroy']);
            });
        });
});
