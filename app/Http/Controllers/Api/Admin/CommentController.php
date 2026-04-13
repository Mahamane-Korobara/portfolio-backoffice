<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    /**
     * Liste des commentaires pour l'admin.
     * GET /api/v1/admin/comments?status=pending
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $comments = Comment::withTrashed()
            // On récupère l'article lié et l'auteur pour l'affichage dans le dashboard
            ->with(['article:id,title,slug', 'user:id,name,avatar'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    /**
     * Approuver un commentaire pour le rendre public.
     * POST /api/v1/admin/comments/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->approve(); // Assure-toi que cette méthode existe dans ton modèle Comment

        return response()->json(['message' => 'Commentaire approuvé.']);
    }

    /**
     * Marquer un commentaire comme spam.
     * POST /api/v1/admin/comments/{id}/spam
     */
    public function spam(int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->markAsSpam(); // Assure-toi que cette méthode existe dans ton modèle Comment

        return response()->json(['message' => 'Marqué comme spam.']);
    }

    /**
     * Supprimer un commentaire.
     * DELETE /api/v1/admin/comments/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        // On utilise findOrFail pour être sûr que le commentaire existe avant de supprimer
        Comment::findOrFail($id)->delete();

        return response()->json(['message' => 'Commentaire supprimé.']);
    }
}
