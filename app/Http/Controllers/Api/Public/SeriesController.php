<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Series;
use Illuminate\Http\JsonResponse;

class SeriesController extends Controller
{
    /**
     * Liste toutes les séries ayant au moins un article publié.
     * GET /api/v1/series
     */
    public function index(): JsonResponse
    {
        $series = Series::withCount(['articles as articles_count' => function ($query) {
            $query->published();
        }])
            ->having('articles_count', '>', 0)
            ->orderBy('title')
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'title'       => $s->title,
                'slug'        => $s->slug,
                'description' => $s->description,
                'cover_image' => $s->cover_image,
                'is_complete' => (bool) $s->is_complete,
                'progress'    => $s->progress, // Assure-toi que c'est un attribut calculé (Accessor)
                'articles_count' => $s->articles_count,
            ]);

        return response()->json($series);
    }

    /**
     * Détails d'une série spécifique et ses articles ordonnés.
     * GET /api/v1/series/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        // On récupère la série par son slug
        $series = Series::where('slug', $slug)->firstOrFail();

        // On ne récupère que les articles publiés, dans l'ordre défini
        $articles = $series->articles()
            ->published()
            ->with(['category', 'tags'])
            ->orderBy('series_order', 'asc')
            ->get();

        return response()->json([
            'id'          => $series->id,
            'title'       => $series->title,
            'slug'        => $series->slug,
            'description' => $series->description,
            'cover_image' => $series->cover_image,
            'is_complete' => (bool) $series->is_complete,
            'progress'    => $series->progress,
            'articles'    => ArticleResource::collection($articles),
        ]);
    }
}
