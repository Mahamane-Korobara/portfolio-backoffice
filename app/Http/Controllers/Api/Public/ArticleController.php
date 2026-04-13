<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleDetailResource;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\ArticleLike;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // GET /api/v1/articles
    // Listing avec filtres : ?tag=laravel&category=devops&page=1&per_page=9
    public function index(Request $request)
    {
        $articles = Article::published()
            ->with(['category', 'tags'])
            ->when(
                $request->category,
                fn($q) =>
                $q->whereHas('category', fn($q) => $q->where('slug', $request->category))
            )
            ->when(
                $request->tag,
                fn($q) =>
                $q->whereHas('tags', fn($q) => $q->where('slug', $request->tag))
            )
            ->when(
                $request->series,
                fn($q) =>
                $q->whereHas('series', fn($q) => $q->where('slug', $request->series))
            )
            ->when(
                $request->search,
                fn($q) =>
                $q->where(
                    fn($q) =>
                    $q->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('excerpt', 'like', '%' . $request->search . '%')
                )
            )
            ->latest('published_at')
            ->paginate($request->get('per_page', 9));

        return ArticleResource::collection($articles);
    }

    // GET /api/v1/articles/{slug}
    public function show(string $slug)
    {
        $article = Article::published()
            ->with(['category', 'tags', 'series', 'approvedComments'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ArticleDetailResource($article);
    }

    // GET /api/v1/articles/{slug}/related
    // 3 articles de la même catégorie ou avec des tags communs
    public function related(string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();

        $tagIds = $article->tags->pluck('id');

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->with(['category', 'tags'])
            ->where(
                fn($q) =>
                $q->where('category_id', $article->category_id)
                    ->orWhereHas('tags', fn($q) => $q->whereIn('id', $tagIds))
            )
            ->latest('published_at')
            ->limit(3)
            ->get();

        return ArticleResource::collection($related);
    }

    // POST /api/v1/articles/{slug}/view
    public function trackView(Request $request, string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        $ipHash  = hash('sha256', $request->ip());

        if (!ArticleView::hasViewed($article->id, $ipHash)) {
            ArticleView::create([
                'article_id'   => $article->id,
                'user_id'      => $request->user()?->id,
                'ip_hash'      => $ipHash,
                'session_id'   => $request->session()->getId(),
                'country'      => $this->getCountry($request),
                'device'       => $this->getDevice($request),
                'browser'      => $this->getBrowser($request),
                'os'           => $this->getOs($request),
                'referrer'     => $request->header('referer'),
                'utm_source'   => $request->get('utm_source'),
                'utm_medium'   => $request->get('utm_medium'),
                'utm_campaign' => $request->get('utm_campaign'),
                'viewed_at'    => now(),
            ]);
            $article->incrementViews();
        }

        return response()->json(['views' => $article->views_count]);
    }

    // POST /api/v1/articles/{slug}/like
    public function toggleLike(Request $request, string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        $ipHash  = hash('sha256', $request->ip());

        $liked = ArticleLike::toggle(
            $article->id,
            $ipHash,
            $request->user()?->id
        );

        return response()->json([
            'liked' => $liked,
            'likes' => $article->fresh()->likes_count,
        ]);
    }

    // GET /api/v1/preview/{token}
    // Accès brouillon via token secret
    public function preview(string $token)
    {
        $article = Article::where('preview_token', $token)
            ->with(['category', 'tags', 'series'])
            ->firstOrFail();

        return new ArticleDetailResource($article);
    }

    // --- Helpers détection device/browser/pays ---

    private function getDevice(Request $request): string
    {
        $ua = $request->userAgent() ?? '';
        if (preg_match('/mobile/i', $ua))  return 'mobile';
        if (preg_match('/tablet/i', $ua))  return 'tablet';
        return 'desktop';
    }

    private function getBrowser(Request $request): string
    {
        $ua = $request->userAgent() ?? '';
        foreach (['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'] as $b) {
            if (str_contains($ua, $b)) return $b;
        }
        return 'Autre';
    }

    private function getOs(Request $request): string
    {
        $ua = $request->userAgent() ?? '';
        foreach (['Windows', 'Mac', 'Linux', 'Android', 'iOS'] as $os) {
            if (str_contains($ua, $os)) return $os;
        }
        return 'Autre';
    }

    private function getCountry(Request $request): ?string
    {
        // Via Cloudflare header si disponible, sinon null
        return $request->header('CF-IPCountry');
    }
}
