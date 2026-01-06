<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArticleView;
use App\Models\Comment;
use Illuminate\Support\Facades\Mail;

class SendDailyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily statistics to moderators';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info("📧 Начинаю отправку ежедневной статистики...");

        $today = now()->startOfDay();
        $viewsCount = ArticleView::where('created_at', '>=', $today)->count();
        $commentsCount = Comment::where('created_at', '>=', $today)->count();

        $moderatorEmail = env('MODERATOR_EMAIL');

        if ($moderatorEmail) {
            try {
                Mail::raw(
                    "Статистика сайта за день:\n\nПросмотров статей: {$viewsCount}\nНовых комментариев: {$commentsCount}",
                    function ($message) use ($moderatorEmail) {
                        $message->to($moderatorEmail)
                                ->subject('Ежедневная статистика сайта');
                    }
                );
                \Log::info("✅ Письмо успешно отправлено на {$moderatorEmail}");
            } catch (\Exception $e) {
                \Log::error("❌ Ошибка отправки: " . $e->getMessage());
            }
        } else {
            \Log::warning("⚠️ Email модератора не указан");
        }
    }
}
