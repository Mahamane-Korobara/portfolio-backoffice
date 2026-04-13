<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Authentification via Google Token.
     * POST /api/v1/auth/google
     */
    public function googleCallback(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');

            // Maintenant Intelephense reconnaîtra stateless() et userFromToken()
            $googleUser = $driver->stateless()->userFromToken($request->token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token Google invalide.'], 401);
        }

        // Création ou mise à jour de l'utilisateur
        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'avatar'            => $googleUser->getAvatar(),
                'role'              => 'visitor', // Rôle par défaut
                'email_verified_at' => now(),
            ]
        );

        // Abonnement automatique à la newsletter s'il n'existe pas déjà
        NewsletterSubscriber::firstOrCreate(
            ['email' => $user->email],
            [
                'user_id'      => $user->id,
                'name'         => $user->name,
                'confirmed'    => true,
                'confirmed_at' => now(),
            ]
        );

        // Génération du Token Sanctum
        $token = $user->createToken('visitor-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar,
                'role'   => $user->role,
            ],
        ]);
    }

    /**
     * Récupérer l'utilisateur connecté.
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    /**
     * Déconnexion (Suppression du token actuel).
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }
}
