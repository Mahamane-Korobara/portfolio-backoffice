<?php

namespace App\Observers;

use App\Models\Article;
use App\Jobs\SendNewArticleNotifications;

class ArticleObserver
{
    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        // On vérifie si le statut a changé et s'il est maintenant "published"
        // wasChanged() est parfait ici pour détecter une modification après sauvegarde
        if ($article->wasChanged('status') && $article->status === 'published') {
            SendNewArticleNotifications::dispatch($article);
        }
    }
}
