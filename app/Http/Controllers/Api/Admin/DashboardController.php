<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Comment;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Chiffres globaux pour les cartes du dashboard.
     * GET /api/v1/admin/stats
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'articles' => [
                'total'     => Article::count(),
                'published' => Article::where('status', 'published')->count(),
                'draft'     => Article::where('status', 'draft')->count(),
                'scheduled' => Article::where('status', 'scheduled')->count(),
            ],
            'views' => [
                'total'   => ArticleView::count(),
                'today'   => ArticleView::whereDate('viewed_at', today())->count(),
                'week'    => ArticleView::where('viewed_at', '>=', now()->subDays(7))->count(),
                'month'   => ArticleView::where('viewed_at', '>=', now()->subDays(30))->count(),
            ],
            'comments' => [
                'total'   => Comment::count(),
                'pending' => Comment::where('status', 'pending')->count(),
            ],
            'subscribers' => NewsletterSubscriber::active()->count(),

            // Top 5 articles par vues
            'top_articles' => Article::published()
                ->orderBy('views_count', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'views_count', 'likes_count', 'comments_count']),
        ]);
    }

    /**
     * Répartition des sources de trafic (referrers).
     * GET /api/v1/admin/stats/sources
     */
    public function sources(): JsonResponse
    {
        $sources = ArticleView::select('referrer', DB::raw('count(*) as total'))
            ->whereNotNull('referrer')
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                // Simplifier l'URL : extraire le domaine
                $host = parse_url($row->referrer, PHP_URL_HOST) ?? $row->referrer;
                return ['source' => $host, 'total' => $row->total];
            });

        return response()->json(['sources' => $sources]);
    }

    /**
     * Répartition par appareils, navigateurs et pays.
     * GET /api/v1/admin/stats/devices
     */
    public function devices(): JsonResponse
    {
        $devices = ArticleView::select('device', DB::raw('count(*) as total'))
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('device')
            ->get();

        $browsers = ArticleView::select('browser', DB::raw('count(*) as total'))
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $countries = ArticleView::select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json([
            'devices'   => $devices,
            'browsers'  => $browsers,
            'countries' => $countries,
        ]);
    }

    /**
     * Courbe de vues sur une période donnée (graphe).
     * GET /api/v1/admin/stats/timeline
     */
    public function timeline(Request $request): JsonResponse
    {
        $days = min((int) $request->get('period', 30), 365);

        $views = ArticleView::select(
            DB::raw('DATE(viewed_at) as date'),
            DB::raw('count(*) as total')
        )
            ->where('viewed_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(viewed_at)'))
            ->orderBy('date')
            ->get();

        return response()->json(['timeline' => $views]);
    }
}
