<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeriesController extends Controller
{
    /**
     * Liste des séries avec le nombre d'articles.
     * GET /api/v1/admin/series
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Series::withCount('articles')->orderBy('title')->get()
        );
    }

    /**
     * Créer une nouvelle série.
     * POST /api/v1/admin/series
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'is_complete' => 'nullable|boolean',
        ]);

        $series = Series::create($data);

        return response()->json($series, 201);
    }

    /**
     * Détails de la série et liste de ses articles ordonnés.
     * GET /api/v1/admin/series/{id}
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(
            Series::withCount('articles')
                ->with(['articles' => function ($query) {
                    $query->select('id', 'title', 'slug', 'series_id', 'series_order', 'status')
                        ->orderBy('series_order', 'asc');
                }])
                ->findOrFail($id)
        );
    }

    /**
     * Mettre à jour une série.
     * PUT /api/v1/admin/series/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $series = Series::findOrFail($id);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'is_complete' => 'nullable|boolean',
        ]);

        $series->update($data);

        return response()->json($series->fresh());
    }

    /**
     * Supprimer une série.
     * DELETE /api/v1/admin/series/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $series = Series::findOrFail($id);

        $series->delete();

        return response()->json(['message' => 'Série supprimée.']);
    }
}
