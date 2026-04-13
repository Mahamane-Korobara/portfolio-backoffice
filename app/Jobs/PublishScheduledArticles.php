<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishScheduledArticles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Article::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->each(function ($article) {
                // Appel de la méthode de publication (à ajouter dans le modèle)
                $article->publish();

                // Envoi des notifications aux abonnés
                SendNewArticleNotifications::dispatch($article);
            });
    }
}
