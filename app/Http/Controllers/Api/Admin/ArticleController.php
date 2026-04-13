<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ArticleAdminResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * Liste complète pour l'admin (inclut les supprimés et les brouillons).
     * GET /api/v1/admin/articles
     */
    public function index(Request $request)
    {
        $articles = Article::withTrashed()
            ->with(['category', 'tags', 'series'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when(
                $request->search,
                fn($q) =>
                $q->where('title', 'like', '%' . $request->search . '%')
            )
            ->latest()
            ->paginate(15);

        return ArticleAdminResource::collection($articles);
    }

    /**
     * Création d'un nouvel article.
     * POST /api/v1/admin/articles
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $article = Article::create($data);

        if (isset($data['tag_ids'])) {
            $article->tags()->sync($data['tag_ids']);
        }

        $this->clearSeoCache();

        return new ArticleAdminResource($article);
    }

    /**
     * Détails d'un article pour édition.
     * GET /api/v1/admin/articles/{id}
     */
    public function show(int $id)
    {
        $article = Article::withTrashed()->with(['category', 'tags', 'series'])->findOrFail($id);
        return new ArticleAdminResource($article);
    }

    /**
     * Mise à jour de l'article.
     * PUT /api/v1/admin/articles/{id}
     */
    public function update(Request $request, int $id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $data    = $this->validated($request, $id);

        $article->update($data);

        if (isset($data['tag_ids'])) {
            $article->tags()->sync($data['tag_ids']);
        }

        $this->clearSeoCache();

        return new ArticleAdminResource($article->fresh());
    }

    /**
     * Suppression temporaire (Soft Delete).
     * DELETE /api/v1/admin/articles/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->delete();
        $this->clearSeoCache();

        return response()->json(['message' => 'Article envoyé à la corbeille.']);
    }

    /**
     * Publication forcée.
     * POST /api/v1/admin/articles/{id}/publish
     */
    public function publish(int $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->publish();
        $this->clearSeoCache();

        return response()->json([
            'message' => 'Article publié.',
            'article' => new ArticleAdminResource($article->fresh())
        ]);
    }

    /**
     * Dépublication (Retour en brouillon).
     * POST /api/v1/admin/articles/{id}/unpublish
     */
    public function unpublish(int $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->unpublish();
        $this->clearSeoCache();

        return response()->json([
            'message' => 'Article repassé en brouillon.',
            'article' => new ArticleAdminResource($article->fresh())
        ]);
    }

    /**
     * Programmation de publication.
     * POST /api/v1/admin/articles/{id}/schedule
     */
    public function schedule(Request $request, int $id): JsonResponse
    {
        $request->validate(['scheduled_at' => 'required|date|after:now']);
        $article = Article::findOrFail($id);
        $article->schedule(Carbon::parse($request->scheduled_at));

        return response()->json(['message' => 'Publication programmée avec succès.']);
    }

    /**
     * Validation centralisée.
     */
    private function validated(Request $request, $id = null): array
    {
        return $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|unique:articles,slug,' . $id,
            'excerpt'          => 'required|string|max:500',
            'content'          => 'required|array',   // JSON TipTap
            'category_id'      => 'nullable|exists:categories,id',
            'series_id'        => 'nullable|exists:series,id',
            'series_order'     => 'nullable|integer|min:1',
            'tag_ids'          => 'nullable|array',
            'tag_ids.*'        => 'exists:tags,id',
            'cover_image'      => 'nullable|string',
            'cover_gallery'    => 'nullable|array',
            'cover_type'       => 'nullable|in:image,gallery',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'canonical_url'    => 'nullable|url',
        ]);
    }

    /**
     * Nettoyage automatique du cache SEO.
     */
    private function clearSeoCache(): void
    {
        Cache::forget('sitemap');
        Cache::forget('rss_feed');
    }
}
