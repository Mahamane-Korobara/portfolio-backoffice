<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Mail\ConfirmSubscriptionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    /**
     * Inscription à la newsletter.
     * POST /api/v1/newsletter/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:100',
        ]);

        // On utilise firstOrCreate pour éviter les doublons
        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'  => $data['name'] ?? null,
                'token' => Str::random(64), // Génère un token si c'est une nouvelle inscription
            ]
        );

        // Si l'utilisateur revient après s'être désabonné
        if ($subscriber->unsubscribed_at) {
            $subscriber->update([
                'unsubscribed_at' => null,
                'token' => Str::random(64) // Nouveau token pour la nouvelle confirmation
            ]);
        }

        // Envoi du mail si non confirmé
        if (!$subscriber->confirmed) {
            Mail::to($subscriber->email)->send(new ConfirmSubscriptionMail($subscriber));
        }

        return response()->json([
            'message' => 'Un email de confirmation a été envoyé à ' . $data['email'],
        ]);
    }

    /**
     * Confirmation de l'abonnement.
     * GET /api/v1/newsletter/confirm/{token}
     */
    public function confirm(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        // Marque comme confirmé et supprime le token (bonne pratique de sécurité)
        $subscriber->confirm();

        $frontendUrl = config('app.frontend_url', 'https://sahelstack.tech');
        return redirect()->away($frontendUrl . '/blog?subscribed=1');
    }

    /**
     * Désabonnement.
     * GET /api/v1/newsletter/unsubscribe/{token}
     */
    public function unsubscribe(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        $subscriber->unsubscribe();

        $frontendUrl = config('app.frontend_url', 'https://sahelstack.tech');
        return redirect()->away($frontendUrl . '/blog?unsubscribed=1');
    }
}
