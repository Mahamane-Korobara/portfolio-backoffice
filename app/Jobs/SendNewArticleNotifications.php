<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\NewsletterSubscriber;
use App\Notifications\NewArticleNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SendNewArticleNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Article $article) {}

    public function handle(): void
    {
        // Abonnés newsletter actifs
        NewsletterSubscriber::active()
            ->cursor()
            ->each(function ($subscriber) {
                $subscriber->notify(new NewArticleNotification($this->article));
            });

        // Utilisateurs connectés avec notify_new_articles = true
        User::subscribed()
            ->whereDoesntHave('newsletterSubscription') // évite le double envoi
            ->cursor()
            ->each(function ($user) {
                $user->notify(new NewArticleNotification($this->article));
            });
    }
}
