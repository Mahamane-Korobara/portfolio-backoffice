<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Liste des catégories pour le public.
     * On ne compte que les articles réellement publiés.
     * * GET /api/v1/categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount(['articles as articles_count' => function ($query) {
            $query->published(); // Utilise le scope "published" défini dans ton modèle Article
        }])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
