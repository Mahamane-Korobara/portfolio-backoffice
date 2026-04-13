<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Liste tous les tags avec le nombre d'articles associés.
     * GET /api/v1/admin/tags
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Tag::withCount('articles')->orderBy('name')->get()
        );
    }

    /**
     * Créer un nouveau tag.
     * POST /api/v1/admin/tags
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
        ]);

        $tag = Tag::create($data);

        return response()->json($tag, 201);
    }

    /**
     * Voir les détails d'un tag.
     * GET /api/v1/admin/tags/{id}
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(
            Tag::withCount('articles')->findOrFail($id)
        );
    }

    /**
     * Mettre à jour un tag.
     * PUT /api/v1/admin/tags/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $id,
        ]);

        $tag->update($data);

        return response()->json($tag->fresh());
    }

    /**
     * Supprimer un tag.
     * DELETE /api/v1/admin/tags/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);

        // Détacher les relations Many-to-Many avant suppression (optionnel selon ta migration)
        $tag->articles()->detach();

        $tag->delete();

        return response()->json(['message' => 'Tag supprimé.']);
    }
}
