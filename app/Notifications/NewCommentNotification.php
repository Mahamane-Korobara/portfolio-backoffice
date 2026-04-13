<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewCommentNotification extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Déterminer le nom de l'auteur (User connecté ou invité)
        $authorName = $this->comment->user ? $this->comment->user->name : $this->comment->guest_name;

        return (new MailMessage)
            ->subject('💬 Nouveau commentaire en attente — ' . $this->comment->article->title)
            ->greeting('Salut !')
            ->line('Un nouveau commentaire a été posté sur ton blog par **' . $authorName . '**.')
            ->line('Article concerné : *' . $this->comment->article->title . '*')
            ->line('Contenu :')
            ->line('> ' . Str::limit($this->comment->body, 200))
            ->action('Modérer le commentaire', url('/admin/comments')) // Ajuste l'URL selon ton besoin
            ->line('Tu peux approuver ou supprimer ce message depuis ton back office.');
    }
}
