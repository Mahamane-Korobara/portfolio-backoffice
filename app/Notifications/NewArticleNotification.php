<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewArticleNotification extends Notification
{
    use Queueable;

    public function __construct(public Article $article) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📝 Nouvel article : ' . $this->article->title)
            ->greeting('Bonjour ' . ($notifiable->name ?? 'there') . ' 👋')
            ->line('Un nouvel article vient d\'être publié sur sahelstack.tech :')
            ->line('**' . $this->article->title . '**')
            ->line($this->article->excerpt)
            ->action('Lire l\'article', $this->article->canonical_url)
            ->line('⏱ Temps de lecture estimé : ' . $this->article->reading_time . ' min')
            ->salutation('— Mahamane, sahelstack.tech')
            ->line('---')
            ->line('Tu reçois cet email car tu es abonné aux nouveaux articles.')
            ->line('[Se désabonner](' . $notifiable->unsubscribe_url . ')');
    }
}
