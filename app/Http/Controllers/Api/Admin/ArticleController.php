<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ArticleAdminResource;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    /**
     * Liste complète pour l'admin.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $articles = Article::withTrashed()
            ->with(['category', 'tags', 'series'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15);

        return ArticleAdminResource::collection($articles);
    }

    /**
     * Création d'un nouvel article.
     */
    public function store(ArticleRequest $request): ArticleAdminResource
    {
        $article = Article::create($request->validated());

        if ($request->has('tag_ids')) {
            $article->tags()->sync($request->tag_ids);
        }

        $this->clearSeoCache();

        return new ArticleAdminResource($article);
    }

    /**
     * Détails d'un article pour édition.
     */
    public function show(int $id): ArticleAdminResource
    {
        $article = Article::withTrashed()->with(['category', 'tags', 'series'])->findOrFail($id);
        return new ArticleAdminResource($article);
    }

    /**
     * Mise à jour de l'article.
     */
    public function update(ArticleRequest $request, int $id): ArticleAdminResource
    {
        $article = Article::withTrashed()->findOrFail($id);

        $article->update($request->validated());

        if ($request->has('tag_ids')) {
            $article->tags()->sync($request->tag_ids);
        }

        $this->clearSeoCache();

        return new ArticleAdminResource($article->fresh());
    }

    /**
     * Suppression temporaire (Soft Delete).
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
     */
    public function schedule(Request $request, int $id): JsonResponse
    {
        $request->validate(['scheduled_at' => 'required|date|after:now']);

        $article = Article::findOrFail($id);
        $article->schedule(Carbon::parse($request->scheduled_at));

        return response()->json(['message' => 'Publication programmée avec succès.']);
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
