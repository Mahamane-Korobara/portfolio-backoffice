<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IMPORTATIONS DES CONTROLLERS
|--------------------------------------------------------------------------
*/

// Controllers Publics
use App\Http\Controllers\Api\Public\ArticleController as PublicArticle;
use App\Http\Controllers\Api\Public\AuthController as PublicAuth;
use App\Http\Controllers\Api\Public\CategoryController as PublicCategory;
use App\Http\Controllers\Api\Public\CommentController as PublicComment;
use App\Http\Controllers\Api\Public\NewsletterController;
use App\Http\Controllers\Api\Public\SeoController;
use App\Http\Controllers\Api\Public\SeriesController as PublicSeries;
use App\Http\Controllers\Api\Public\TagController as PublicTag;

// Controllers Admin
use App\Http\Controllers\Api\Admin\ArticleController as AdminArticle;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategory;
use App\Http\Controllers\Api\Admin\CommentController as AdminComment;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\MediaController;
use App\Http\Controllers\Api\Admin\SubscriberController;
use App\Http\Controllers\Api\Admin\SeriesController as AdminSeries;
use App\Http\Controllers\Api\Admin\TagController as AdminTag;

/*
|--------------------------------------------------------------------------
| ROUTES API — VERSION 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ROUTES PUBLIQUES (Next.js frontend)
    |--------------------------------------------------------------------------
    */

    // Articles
    Route::get('/articles',                [PublicArticle::class, 'index']);
    Route::get('/articles/{slug}',         [PublicArticle::class, 'show']);
    Route::get('/articles/{slug}/related', [PublicArticle::class, 'related']);
    Route::post('/articles/{slug}/view',   [PublicArticle::class, 'trackView']);
    Route::post('/articles/{slug}/like',   [PublicArticle::class, 'toggleLike'])->middleware('throttle:likes');

    // Prévisualisation (Brouillon)
    Route::get('/preview/{token}', [PublicArticle::class, 'preview']);

    // Commentaires
    Route::get('/articles/{slug}/comments',  [PublicComment::class, 'index']);
    Route::post('/articles/{slug}/comments', [PublicComment::class, 'store'])->middleware('throttle:comments');

    // Catégories & Tags
    Route::get('/categories', [PublicCategory::class, 'index']);
    Route::get('/tags',        [PublicTag::class, 'index']);

    // Séries
    Route::get('/series',       [PublicSeries::class, 'index']);
    Route::get('/series/{slug}', [PublicSeries::class, 'show']);

    // Newsletter
    Route::post('/newsletter/subscribe',          [NewsletterController::class, 'subscribe']);
    Route::get('/newsletter/confirm/{token}',     [NewsletterController::class, 'confirm']);
    Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe']);

    // SEO
    Route::get('/sitemap', [SeoController::class, 'sitemap']);
    Route::get('/feed',    [SeoController::class, 'rss']);

    // Auth Visiteurs
    Route::post('/auth/google',  [PublicAuth::class, 'googleCallback']);
    Route::post('/auth/logout',  [PublicAuth::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/auth/me',       [PublicAuth::class, 'me'])->middleware('auth:sanctum');

    /*
    |--------------------------------------------------------------------------
    | ROUTES PRIVÉES ADMIN (dashboard.sahelstack.tech)
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->group(function () {

        // Auth Admin
        Route::post('/login',  [AdminAuth::class, 'login'])->middleware('throttle:admin-login');

        // Middleware restreint à l'admin
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

            Route::post('/logout', [AdminAuth::class, 'logout']);

            // Dashboard
            Route::get('/stats',          [DashboardController::class, 'stats']);
            Route::get('/stats/sources',  [DashboardController::class, 'sources']);
            Route::get('/stats/devices',  [DashboardController::class, 'devices']);
            Route::get('/stats/timeline', [DashboardController::class, 'timeline']);

            // Articles Management
            Route::apiResource('/articles', AdminArticle::class);
            Route::post('/articles/{id}/publish',   [AdminArticle::class, 'publish']);
            Route::post('/articles/{id}/unpublish', [AdminArticle::class, 'unpublish']);
            Route::post('/articles/{id}/schedule',  [AdminArticle::class, 'schedule']);

            // Commentaires Management
            Route::get('/comments',               [AdminComment::class, 'index']);
            Route::post('/comments/{id}/approve', [AdminComment::class, 'approve']);
            Route::post('/comments/{id}/spam',    [AdminComment::class, 'spam']);
            Route::delete('/comments/{id}',       [AdminComment::class, 'destroy']);

            // Médias
            Route::get('/media',           [MediaController::class, 'index']);
            Route::post('/media',          [MediaController::class, 'store']);
            Route::delete('/media/{id}',   [MediaController::class, 'destroy']);

            // Taxonomies
            Route::apiResource('/categories', AdminCategory::class);
            Route::apiResource('/tags',        AdminTag::class);
            Route::apiResource('/series',      AdminSeries::class);

            // Abonnés
            Route::get('/subscribers',         [SubscriberController::class, 'index']);
            Route::delete('/subscribers/{id}', [SubscriberController::class, 'destroy']);
        });
    });
});
