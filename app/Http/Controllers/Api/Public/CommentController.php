<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET /api/v1/articles/{slug}/comments
    public function index(string $slug)
    {
        $article  = Article::published()->where('slug', $slug)->firstOrFail();

        // Utilise la relation approvedComments que nous avons définie dans le modèle Article
        $comments = $article->approvedComments()->get();

        return CommentResource::collection($comments);
    }

    // POST /api/v1/articles/{slug}/comments
    public function store(Request $request, string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'body'        => 'required|string|min:3|max:2000',
            'parent_id'   => 'nullable|exists:comments,id',
            'guest_name'  => 'required_without:user|nullable|string|max:100',
            'guest_email' => 'nullable|email|max:255',
        ]);

        $comment = Comment::create([
            'article_id'  => $article->id,
            'user_id'     => $request->user()?->id,
            'parent_id'   => $data['parent_id'] ?? null,
            'guest_name'  => $request->user() ? null : $data['guest_name'],
            'guest_email' => $request->user() ? null : ($data['guest_email'] ?? null),
            'body'        => $data['body'],
            'status'      => 'pending', // Toujours en attente de modération par défaut
        ]);

        return response()->json([
            'message' => 'Commentaire soumis. Il sera visible après modération.',
            'comment' => new CommentResource($comment),
        ], 201);
    }
}
