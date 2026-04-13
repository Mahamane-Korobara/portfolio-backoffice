<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Liste des tags populaires ayant au moins un article publié.
     * GET /api/v1/tags
     */
    public function index(): JsonResponse
    {
        $tags = Tag::withCount(['articles as articles_count' => function ($query) {
            $query->published(); // Filtre pour ne compter que le contenu public
        }])
            ->having('articles_count', '>', 0)
            ->orderByDesc('articles_count') // Les plus utilisés en premier
            ->get();

        return response()->json($tags);
    }
}
