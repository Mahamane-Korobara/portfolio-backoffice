<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Liste des catégories avec le nombre d'articles rattachés.
     * GET /api/v1/admin/categories
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Category::withCount('articles')->orderBy('name')->get()
        );
    }

    /**
     * Créer une nouvelle catégorie.
     * POST /api/v1/admin/categories
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100|unique:categories,name',
            'color' => 'nullable|string|max:7', // Format Hexadécimal (#FFFFFF)
        ]);

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /**
     * Détails d'une catégorie.
     * GET /api/v1/admin/categories/{id}
     */
    public function show(int $id): JsonResponse
    {
        return response()->json(
            Category::withCount('articles')->findOrFail($id)
        );
    }

    /**
     * Mettre à jour une catégorie.
     * PUT /api/v1/admin/categories/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name'  => 'required|string|max:100|unique:categories,name,' . $id,
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($data);

        return response()->json($category->fresh());
    }

    /**
     * Supprimer une catégorie.
     * DELETE /api/v1/admin/categories/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        Category::findOrFail($id)->delete();

        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
