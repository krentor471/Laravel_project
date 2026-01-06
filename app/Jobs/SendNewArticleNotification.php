<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewArticleNotification;
use App\Mail\UserArticleNotification;

class SendNewArticleNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function handle()
    {
        // Отправка уведомления модератору
        $moderatorEmail = env('MODERATOR_EMAIL', 'moderator@example.com');
        Mail::to($moderatorEmail)->send(new NewArticleNotification($this->article));

        // Отправка уведомлений всем пользователям
        $users = User::all();
        foreach ($users as $user) {
            if ($user->email) {
                Mail::to($user->email)->send(new UserArticleNotification($this->article));
            }
        }
    }
}
