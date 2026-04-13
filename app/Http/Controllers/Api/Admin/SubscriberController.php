<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;

class SubscriberController extends Controller
{
    /**
     * Liste des abonnés à la newsletter avec pagination.
     * GET /api/v1/admin/subscribers
     */
    public function index(): JsonResponse
    {
        $subscribers = NewsletterSubscriber::with('user:id,name,avatar')
            ->latest()
            ->paginate(30);

        return response()->json($subscribers);
    }

    /**
     * Supprimer un abonné de la liste.
     * DELETE /api/v1/admin/subscribers/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);

        $subscriber->delete();

        return response()->json(['message' => 'Abonné supprimé avec succès.']);
    }
}
