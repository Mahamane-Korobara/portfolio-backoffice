<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\NewCommentNotification;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        // On utilise le scope admins() défini dans ton modèle User
        // pour trouver le premier administrateur et lui envoyer la notification
        User::admins()->first()?->notify(new NewCommentNotification($comment));
    }
}
