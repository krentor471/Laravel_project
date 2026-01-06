<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;


// В самом начале файла, после use statements
Route::get('/debug-mail', function () {
    // Включаем лог для email
    config(['mail.default' => 'log']);
    
    \Log::info('=== Начинаем тест email ===');
    
    // Создаем или получаем тестовые данные
    $article = \App\Models\Article::first();
    if (!$article) {
        $article = \App\Models\Article::create([
            'title' => 'Тестовая статья для email',
            'content' => 'Содержание тестовой статьи'
        ]);
    }
    
    $user = \App\Models\User::first();
    if (!$user) {
        $user = \App\Models\User::create([
            'name' => 'Тестовый автор',
            'email' => 'author@example.com',
            'password' => bcrypt('password')
        ]);
    }
    
    // Создаем модератора
    $moderator = \App\Models\User::where('email', 'karav1902@mail.ru')->first();
    if (!$moderator) {
        // Создаем роль moderator если нет
        $role = \App\Models\Role::firstOrCreate(['name' => 'moderator']);
        
        $moderator = \App\Models\User::create([
            'name' => 'Модератор Тестовый',
            'email' => 'karav1902@mail.ru',
            'password' => bcrypt('password'),
            'role_id' => $role->id
        ]);
    }
    
    // Создаем комментарий
    $comment = \App\Models\Comment::create([
        'article_id' => $article->id,
        'user_id' => $user->id,
        'content' => 'Тестовый комментарий для проверки email отправки. Проверяем работу mail.ru SMTP.'
    ]);
    
    \Log::info('Данные для теста:', [
        'article_id' => $article->id,
        'article_title' => $article->title,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'moderator_email' => $moderator->email,
        'comment_id' => $comment->id
    ]);
    
    try {
        // Пробуем отправить email
        \Illuminate\Support\Facades\Mail::to($moderator->email)
            ->send(new \App\Mail\NewCommentNotification($comment));
        
        \Log::info('Email отправлен успешно!');
        return response()->json([
            'status' => 'success',
            'message' => 'Email отправлен на ' . $moderator->email,
            'check_log' => 'storage/logs/laravel.log',
            'moderator' => $moderator->email,
            'comment_id' => $comment->id
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка отправки email: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Работа 1 + Работа 2: Главная страница с JSON
Route::get('/', [MainController::class, 'index'])->name('home');

// Работа 1: О нас
Route::get('/about', function () {
    return view('about');
})->name('about');

// Работа 1: Контакты
Route::get('/contacts', function () {
    $contacts = [
        [
            'person' => 'Воротилин Илья Андреевич',
            'phone' => '241-321',
            'email' => 'vorotilin@example.com'
        ],
        [
            'person' => 'Петров Петр Петрович',
            'phone' => '242-322',
            'email' => 'petrov@example.com'
        ],
        [
            'person' => 'Сидоров Сидор Сидорович',
            'phone' => '243-323',
            'email' => 'sidorov@example.com'
        ]
    ];

    return view('contacts', ['contacts' => $contacts]);
})->name('contacts');

// Работа 2: Галерея
Route::get('/galery/{id}', [MainController::class, 'galery'])->name('galery');

Route::get('/signin', [AuthController::class, 'create'])->name('signin');
Route::post('/signin', [AuthController::class, 'registration'])->name('registration');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* Route::middleware('auth:sanctum')->group(function () {
    Route::resource('articles', ArticleController::class)->except(['index', 'show']);
}); */

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show')->middleware('log.article.views');

// Маршруты для комментариев
Route::post('/articles/{article}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

// Маршруты модерации комментариев
Route::middleware(['auth', 'moderator'])->group(function () {
    Route::get('/comments/moderation', [CommentController::class, 'moderation'])->name('comments.moderation');
    Route::post('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
    Route::post('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
});

// Маршрут для уведомлений
// Route::middleware(['auth'])->group(function () {
//     Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notification.read');
// });

Route::middleware('auth')->group(function () {
    Route::resource('articles', ArticleController::class)->except(['index', 'show']);
});

Route::get('/article/create', [ArticleController::class, 'create'])->name('articles.create');

Route::get('/notifications/{id}', [NotificationController::class, 'read'])->name('notification.read');

Route::get('/notifications/{id}', function ($id) {
    return "Читаем уведомление №{$id}";
})->name('notification.read');

Route::get('/test-mail-simple', function () {
    echo "<h3>Конфигурация почты:</h3>";
    
    // Проверяем env переменные напрямую
    $envVars = [
        'MAIL_MAILER' => $_ENV['MAIL_MAILER'] ?? 'не найден',
        'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? 'не найден',
        'MAIL_PORT' => $_ENV['MAIL_PORT'] ?? 'не найден',
        'MAIL_USERNAME' => $_ENV['MAIL_USERNAME'] ?? 'не найден',
        'MAIL_FROM_ADDRESS' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'не найден',
        'MODERATOR_EMAIL' => $_ENV['MODERATOR_EMAIL'] ?? 'не найден',
    ];
    
    foreach ($envVars as $key => $value) {
        echo "$key: " . htmlspecialchars($value) . "<br>";
    }
    echo "<br>";
    
    // Тест 1: Простое письмо на фиксированный адрес
    try {
        \Illuminate\Support\Facades\Mail::raw('Тестовое письмо из Laravel', function($message) {
            $message->to('karav1902@mail.ru')
                    ->subject('Тест почты ' . now())
                    ->from('karav1902@mail.ru', 'Laravel Test');
        });
        
        echo "✅ Простое письмо отправлено на karav1902@mail.ru<br>";
    } catch (\Exception $e) {
        echo "❌ Ошибка простого письма: " . $e->getMessage() . "<br>";
    }
    
    // Тест 2: Mailable
    echo "<h3>Тест через Mailable:</h3>";
    $comment = \App\Models\Comment::first();
    
    if ($comment) {
        try {
            \Illuminate\Support\Facades\Mail::to('karav1902@mail.ru')
                ->send(new \App\Mail\NewCommentNotification($comment));
            echo "✅ Mailable отправлен на karav1902@mail.ru";
        } catch (\Exception $e) {
            echo "❌ Ошибка Mailable: " . $e->getMessage();
        }
    } else {
        echo "Создайте сначала комментарий в базе";
    }
});