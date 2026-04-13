<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Connexion administrative.
     * POST /api/v1/admin/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // On cherche l'utilisateur uniquement s'il est admin
        $user = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();

        // Vérification du mot de passe
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects ou accès non autorisé.'],
            ]);
        }

        // Sécurité : Supprimer les anciens tokens pour n'avoir qu'une session active
        $user->tokens()->delete();

        // Création du token avec l'ability spécifique
        $token = $user->createToken('admin-token', ['role:admin'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email
            ],
        ]);
    }

    /**
     * Déconnexion administrative.
     * POST /api/v1/admin/logout
     */
    public function logout(Request $request): JsonResponse
    {
        // On nettoie tous les tokens de l'admin
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }
}
