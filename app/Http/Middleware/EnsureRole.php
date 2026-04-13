<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifie si l'utilisateur est connecté ET s'il possède le rôle requis
        if (!$request->user() || $request->user()->role !== $role) {
            return response()->json([
                'message' => 'Accès non autorisé. Vous devez être ' . $role . '.'
            ], 403);
        }

        return $next($request);
    }
}
