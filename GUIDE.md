# Шпаргалка по выполнению работ

## Структура проекта

### Основные папки и их назначение

**app/** - основная логика приложения
- **Console/Commands/** - консольные команды (SendDailyStatistics.php)
- **Events/** - события Laravel (NewArticleEvent.php для broadcasting)
- **Http/Controllers/** - контроллеры приложения
  - **API/** - API контроллеры для REST API (ArticleController, AuthController)
  - ArticleController.php - управление статьями
  - CommentController.php - управление комментариями
  - AuthController.php - аутентификация и регистрация
  - NotificationController.php - работа с уведомлениями
- **Http/Middleware/** - посредники (LogArticleViews.php для логирования просмотров)
- **Jobs/** - задания для очереди (SendNewArticleNotification.php)
- **Mail/** - классы для отправки email (NewArticleNotification, UserArticleNotification)
- **Models/** - модели Eloquent (Article, Comment, User, Role, ArticleView)
- **Notifications/** - уведомления Laravel (NewArticleNotification для database notifications)
- **Policies/** - политики авторизации (ArticlePolicy)
- **Providers/** - сервис-провайдеры (AuthServiceProvider, BroadcastServiceProvider)

**config/** - конфигурационные файлы
- app.php - основная конфигурация (зарегистрирован BroadcastServiceProvider)
- broadcasting.php - настройки broadcasting (Pusher)
- cache.php - настройки кеша
- database.php - настройки БД
- mail.php - настройки почты
- queue.php - настройки очередей

**database/** - база данных
- **migrations/** - миграции для создания таблиц
  - create_users_table (пользователи)
  - create_articles_table (статьи)
  - create_roles_table (роли)
  - add_role_id_to_users_table (связь пользователей с ролями)
  - create_comments_table (комментарии)
  - add_user_id_to_comments_table (автор комментария)
  - add_is_approved_to_comments_table (модерация)
  - create_jobs_table (очередь заданий)
  - create_notifications_table (уведомления в БД)
  - create_cache_table (кеш в БД)
  - create_article_views_table (логи просмотров)
- **factories/** - фабрики для генерации тестовых данных (ArticleFactory, CommentFactory)
- **seeders/** - сидеры для заполнения БД (RoleSeeder создает роли и модератора)

**resources/** - ресурсы фронтенда
- **js/** - JavaScript файлы
  - **components/** - Vue компоненты (App.vue для пуш-уведомлений)
  - app.js - главный файл приложения, подключает Vue
  - bootstrap.js - настройка Laravel Echo для Pusher
- **views/** - Blade шаблоны
  - **articles/** - шаблоны статей (index, show, create, edit)
  - **auth/** - шаблоны авторизации (login, signin)
  - **comments/** - шаблоны комментариев (edit, moderation)
  - **emails/** - шаблоны писем (new-article для модератора, user-article для читателей)
  - **layouts/** - общие макеты (app.blade.php с навигацией и уведомлениями)

**routes/** - маршруты
- web.php - веб-маршруты (статьи, комментарии, авторизация)
- api.php - API маршруты (REST API для статей и авторизации)
- channels.php - каналы broadcasting

**public/** - публичные файлы
- **css/**, **js/** - скомпилированные CSS и JS (через webpack)
- style.css - основные стили проекта
- mix-manifest.json - манифест Laravel Mix

**vendor/** - зависимости Composer
- **laravel/framework/src/Illuminate/** - компоненты Laravel (Auth, Cache, Mail, Queue, Broadcasting и т.д.)

**Системные файлы:**
- .env - переменные окружения (MAIL, PUSHER, CACHE, QUEUE настройки)
- composer.json - зависимости PHP
- package.json - зависимости NPM (Vue 3, Laravel Echo, Pusher)
- webpack.mix.js - конфигурация сборки фронтенда

### Ключевые настройки в .env

```env
BROADCAST_DRIVER=pusher
CACHE_DRIVER=database
QUEUE_CONNECTION=database

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=eu

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.ru
MAIL_PORT=465
MAIL_USERNAME=your-email@mail.ru
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@mail.ru
MODERATOR_EMAIL=moderator@example.com
```

### Где находятся кеш и другие данные

**Кеш (CACHE_DRIVER=database):**
- Хранится в таблице `cache` в БД
- Используется в ArticleController для кеширования списка статей и отдельных статей
- Очищается при создании/обновлении/удалении статей

**Очередь (QUEUE_CONNECTION=database):**
- Хранится в таблице `jobs` в БД
- Задания добавляются через `dispatch()` (SendNewArticleNotification)
- Обрабатываются командой `php artisan queue:work`

**Уведомления (Database Notifications):**
- Хранятся в таблице `notifications` в БД
- Создаются через `Notification::send()`
- Отображаются в выпадающем списке в навигации

**Логи просмотров статей:**
- Хранятся в таблице `article_views` в БД
- Записываются через middleware LogArticleViews
- Используются в команде SendDailyStatistics

---

## Домашнее задание 3: Работа с комментариями

### Создание модели, миграции, контроллера и фабрики
**Требование:** Создать новую модель Comment с миграцией, контроллером и фабрикой.

**Теория:** Laravel позволяет создавать все необходимые файлы одной командой используя флаги: `-m` (миграция), `-c` (контроллер), `-f` (фабрика).

**Реализация:**
```bash
php artisan make:model Comment -mcf
```

Это создает:
- `app/Models/Comment.php` - модель
- `database/migrations/xxxx_create_comments_table.php` - миграция
- `app/Http/Controllers/CommentController.php` - контроллер
- `database/factories/CommentFactory.php` - фабрика

### Заполнение миграции
**Требование:** Создать таблицу comments со связью с articles.

**Теория:** Миграции описывают структуру таблиц БД. `foreignId()->constrained()` создает внешний ключ с каскадным удалением.

**Реализация:** В файле миграции `create_comments_table.php`:
```php
$table->id();
$table->foreignId('article_id')->constrained()->onDelete('cascade');
$table->text('content');
$table->timestamps();
```

### Настройка отношений между моделями
**Требование:** Настроить связи Article ↔ Comment.

**Теория:** Eloquent ORM использует методы для определения отношений: `hasMany()` (один ко многим), `belongsTo()` (принадлежит к).

**Реализация:**

В `app/Models/Article.php`:
```php
public function comments()
{
    return $this->hasMany(Comment::class);
}
```

В `app/Models/Comment.php`:
```php
public function article()
{
    return $this->belongsTo(Article::class);
}
```

### Заполнение фабрики
**Требование:** Создать фабрику для генерации тестовых комментариев.

**Теория:** Фабрики используют Faker для генерации случайных данных.

**Реализация:** В `database/factories/CommentFactory.php`:
```php
public function definition()
{
    return [
        'article_id' => \App\Models\Article::factory(),
        'content' => $this->faker->paragraph(),
    ];
}
```

### CRUD методы для комментариев
**Требование:** Реализовать Create, Read, Update, Delete для комментариев.

**Теория:** RESTful подход: store (создание), edit/update (редактирование), destroy (удаление).

**Реализация:** В `app/Http/Controllers/CommentController.php`:
```php
// Создание
public function store(Request $request, $articleId) {
    $validated = $request->validate(['content' => 'required|string|min:3']);
    $article = Article::findOrFail($articleId);
    $article->comments()->create($validated);
    return redirect()->route('articles.show', $articleId);
}

// Редактирование
public function edit($id) {
    $comment = Comment::findOrFail($id);
    return view('comments.edit', compact('comment'));
}

// Обновление
public function update(Request $request, $id) {
    $comment = Comment::findOrFail($id);
    $validated = $request->validate(['content' => 'required|string|min:3']);
    $comment->update($validated);
    return redirect()->route('articles.show', $comment->article_id);
}

// Удаление
public function destroy($id) {
    $comment = Comment::findOrFail($id);
    $articleId = $comment->article_id;
    $comment->delete();
    return redirect()->route('articles.show', $articleId);
}
```

### Каскадное удаление
**Требование:** При удалении статьи удалять все комментарии.

**Теория:** `onDelete('cascade')` в миграции автоматически удаляет связанные записи.

**Реализация:** Уже реализовано в миграции через `->onDelete('cascade')`.

---

## Домашнее задание 4: Авторизация комментариев

### Добавление автора комментария
**Требование:** Добавить поле user_id в таблицу comments.

**Теория:** Для добавления поля в существующую таблицу создается новая миграция с `--table` флагом.

**Реализация:**
```bash
php artisan make:migration add_user_id_to_comments_table --table=comments
```

В миграции:
```php
$table->foreignId('user_id')->nullable()->after('article_id')->constrained()->onDelete('cascade');
```

Обновить модель Comment:
```php
protected $fillable = ['article_id', 'user_id', 'content'];

public function user()
{
    return $this->belongsTo(User::class);
}
```

### Настройка Gates для авторизации
**Требование:** Читатель может добавлять комментарии, автор - редактировать/удалять свои.

**Теория:** Gates - механизм авторизации Laravel. Определяются в `AuthServiceProvider`.

**Реализация:** В `app/Providers/AuthServiceProvider.php`:
```php
Gate::define('update-comment', function ($user, $comment) {
    return $user->id === $comment->user_id;
});

Gate::define('delete-comment', function ($user, $comment) {
    return $user->id === $comment->user_id;
});
```

В контроллере:
```php
$this->authorize('update-comment', $comment);
```

В Blade:
```blade
@can('update-comment', $comment)
    <a href="{{ route('comments.edit', $comment->id) }}">Редактировать</a>
@endcan
```

---

## Работа 8: Email рассылка

### Создание Mail класса
**Требование:** Создать класс для отправки уведомлений о новой статье.

**Теория:** Laravel Mail - система для отправки email с использованием Mailable классов.

**Реализация:**
```bash
php artisan make:mail NewArticleNotification
```

В `app/Mail/NewArticleNotification.php`:
```php
public $article;

public function __construct(Article $article)
{
    $this->article = $article;
}

public function build()
{
    return $this->subject('Новая статья: ' . $this->article->title)
                ->view('emails.new-article');
}
```

### Создание шаблона письма
**Требование:** Сверстать Blade шаблон для email.

**Реализация:** Создать `resources/views/emails/new-article.blade.php`:
```blade
<h1>Добавлена новая статья</h1>
<h2>{{ $article->title }}</h2>
<p><strong>Автор:</strong> {{ $article->author ?? 'Неизвестен' }}</p>
<p>{{ $article->content }}</p>
```

### Отправка email
**Требование:** Отправлять email модератору при создании статьи.

**Теория:** Фасад Mail используется для отправки писем.

**Реализация:** В `ArticleController@store`:
```php
use Illuminate\Support\Facades\Mail;
use App\Mail\NewArticleNotification;

$article = Article::create($validated);
$moderatorEmail = env('MODERATOR_EMAIL', 'moderator@example.com');
Mail::to($moderatorEmail)->send(new NewArticleNotification($article));
```

### Настройка окружения
**Требование:** Настроить SMTP для отправки через mail.ru.

**Реализация:** В `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.ru
MAIL_PORT=465
MAIL_USERNAME=your-email@mail.ru
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@mail.ru
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Работа 9: Модерация комментариев

### Добавление поля модерации
**Требование:** Добавить поле is_approved в таблицу comments.

**Реализация:**
```bash
php artisan make:migration add_is_approved_to_comments_table --table=comments
```

В миграции:
```php
$table->boolean('is_approved')->default(false)->after('content');
```

### Интерфейс модерации
**Требование:** Страница со списком комментариев на модерации (только для модератора).

**Реализация:**

Методы в `CommentController`:
```php
// Список комментариев на модерации
public function moderation()
{
    $comments = Comment::where('is_approved', false)
                       ->with(['article', 'user'])
                       ->orderBy('created_at', 'desc')
                       ->get();
    return view('comments.moderation', compact('comments'));
}

// Одобрение
public function approve($id)
{
    $comment = Comment::findOrFail($id);
    $comment->update(['is_approved' => true]);
    return redirect()->back();
}

// Отклонение
public function reject($id)
{
    $comment = Comment::findOrFail($id);
    $comment->delete();
    return redirect()->back();
}
```

### Фильтрация комментариев
**Требование:** Показывать только одобренные комментарии на странице статьи.

**Реализация:** В `articles/show.blade.php`:
```blade
@php
    $approvedComments = $article->comments->where('is_approved', true);
@endphp

@foreach($approvedComments as $comment)
    <!-- Отображение комментария -->
@endforeach
```

---

## Работа 10: Очереди Laravel

### Создание таблицы очередей
**Требование:** Создать таблицу для хранения заданий.

**Теория:** Laravel Queues позволяет отложить выполнение задач (например, отправку email).

**Реализация:**
```bash
php artisan queue:table
```

Это создает миграцию для таблицы jobs.

### Настройка драйвера очереди
**Требование:** Использовать database драйвер.

**Реализация:** В `.env`:
```env
QUEUE_CONNECTION=database
```

### Создание Job
**Требование:** Создать задание для отправки email.

**Реализация:**
```bash
php artisan make:job SendNewArticleNotification
```

В `app/Jobs/SendNewArticleNotification.php`:
```php
public $article;

public function __construct(Article $article)
{
    $this->article = $article;
}

public function handle()
{
    $moderatorEmail = env('MODERATOR_EMAIL', 'moderator@example.com');
    Mail::to($moderatorEmail)->send(new NewArticleNotification($this->article));
}
```

### Использование Job
**Требование:** Отправлять email через очередь.

**Реализация:** В `ArticleController@store`:
```php
use App\Jobs\SendNewArticleNotification;

$article = Article::create($validated);
SendNewArticleNotification::dispatch($article);
```

### Запуск обработчика очереди
**Требование:** Запустить worker для обработки заданий.

**Реализация:**
```bash
php artisan queue:work
```

Worker будет обрабатывать задания из очереди в фоновом режиме.

---

## Полезные команды

### Миграции
```bash
php artisan migrate                 # Выполнить миграции
php artisan migrate:fresh           # Пересоздать БД
php artisan migrate:fresh --seed    # Пересоздать БД и выполнить сиды
```

### Создание файлов
```bash
php artisan make:model Name -mcf    # Модель + миграция + контроллер + фабрика
php artisan make:migration name     # Миграция
php artisan make:controller Name    # Контроллер
php artisan make:mail Name          # Mail класс
php artisan make:job Name           # Job
```

### Очереди
```bash
php artisan queue:table             # Создать миграцию для очередей
php artisan queue:work              # Запустить worker
php artisan queue:failed-table      # Таблица для failed jobs
```

---

## Работа 11: Пуш-уведомления (Broadcasting)

### Установка зависимостей
**Требование:** Настроить Laravel Broadcasting с Pusher для real-time уведомлений.

**Теория:** Laravel Broadcasting позволяет транслировать события на клиентскую сторону через WebSockets. Pusher - облачный сервис для real-time коммуникации.

**Реализация:**
```bash
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js
npm install vue@3
npm install --save-dev vue-loader@next @vue/compiler-sfc
```

### Настройка окружения
**Требование:** Активировать BroadcastServiceProvider и настроить Pusher.

**Реализация:**

В `config/app.php` раскомментировать:
```php
App\Providers\BroadcastServiceProvider::class,
```

В `config/broadcasting.php`:
```php
'default' => env('BROADCAST_DRIVER', 'pusher'),
```

В `.env`:
```env
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=eu
```

### Создание события (Event)
**Требование:** Создать событие, которое будет транслироваться при создании статьи.

**Реализация:**
```bash
php artisan make:event NewArticleEvent
```

В `app/Events/NewArticleEvent.php`:
```php
class NewArticleEvent implements ShouldBroadcast
{
    public $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function broadcastOn()
    {
        return new Channel('test');
    }

    public function broadcastWith()
    {
        return [
            'article' => [
                'id' => $this->article->id,
                'name' => $this->article->title,
            ]
        ];
    }
}
```

### Настройка Echo в Bootstrap
**Требование:** Раскомментировать настройки Echo для прослушивания событий.

**Реализация:** В `resources/js/bootstrap.js`:
```javascript
import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

### Создание Vue компонента
**Требование:** Создать Vue компонент для отображения пуш-уведомлений.

**Реализация:**

Обновить `webpack.mix.js`:
```javascript
mix.js('resources/js/app.js', 'public/js')
    .vue()
    .postCss('resources/css/app.css', 'public/css');
```

В `resources/js/app.js`:
```javascript
import { createApp } from 'vue';
import App from './components/App.vue';

const app = createApp(App);
app.mount('#app');
```

Создать `resources/js/components/App.vue`:
```vue
<template>
    <div v-if="article != null" class="alert alert-primary" role="alert">
        Добавлена новая статья <strong> <a :href="`/article/${article.id}`"> {{ article.name }}</a></strong>
    </div>
</template>

<script>
    export default {
    data() { return { article: null } },
        created() {
            window.Echo.channel('test').listen('NewArticleEvent', (article) => {
                console.log(article);
                this.article=article.article;
            })
        }
    }
</script>
```

### Интеграция в Layout
**Требование:** Подключить Vue компонент к основному layout.

**Реализация:** В `resources/views/layouts/app.blade.php`:
```blade
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <div id="app">
        <!-- Main content -->
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
```

### Вызов события
**Требование:** Отправлять пуш-уведомление при создании статьи.

**Реализация:** В `ArticleController@store`:
```php
use App\Events\NewArticleEvent;

$article = Article::create($validated);
event(new NewArticleEvent($article));
```

### Сборка фронтенда
**Требование:** Собрать JS и CSS файлы.

**Реализация:**
```bash
npm run dev
npm run watch
```

---

## Работа 12: Database Notifications

### Создание таблицы уведомлений
**Требование:** Создать таблицу для хранения уведомлений в БД.

**Теория:** Laravel Notifications поддерживает различные каналы: mail, database, broadcast, SMS и др.

**Реализация:**
```bash
php artisan notifications:table
php artisan migrate
```

### Создание Notification класса
**Требование:** Создать класс уведомления для информирования читателей о новых статьях.

**Реализация:**
```bash
php artisan make:notification NewArticleNotification
```

В `app/Notifications/NewArticleNotification.php`:
```php
class NewArticleNotification extends Notification
{
    protected $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'article_id' => $this->article->id,
            'article_title' => $this->article->title,
            'message' => 'Добавлена новая статья: ' . $this->article->title,
        ];
    }
}
```

### Отправка уведомлений
**Требование:** Отправлять уведомления всем читателям при создании статьи.

**Реализация:** В `ArticleController@store`:
```php
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\NewArticleNotification;

$readers = User::all();
Notification::send($readers, new NewArticleNotification($article));
```

### Панель уведомлений в навигации
**Требование:** Добавить выпадающий список с непрочитанными уведомлениями.

**Реализация:** В `layouts/app.blade.php`:
```blade
@auth
    <div class="notifications-dropdown">
        <button class="notifications-btn">
            Уведомления
            @if(Auth::user()->unreadNotifications->count() > 0)
                <span class="badge">{{ Auth::user()->unreadNotifications->count() }}</span>
            @endif
        </button>
        <div class="notifications-list">
            @forelse(Auth::user()->unreadNotifications as $notification)
                <a href="{{ route('notification.read', $notification->id) }}" class="notification-item">
                    {{ $notification->data['message'] ?? 'Новое уведомление' }}
                </a>
            @empty
                <div class="notification-item">Нет новых уведомлений</div>
            @endforelse
        </div>
    </div>
@endauth
```

### Контроллер для пометки прочитанным
**Требование:** При клике на уведомление - открыть статью и пометить уведомление прочитанным.

**Реализация:**
```bash
php artisan make:controller NotificationController
```

В `NotificationController`:
```php
public function markAsRead($id)
{
    $notification = auth()->user()->notifications()->findOrFail($id);
    $articleId = $notification->data['article_id'] ?? null;
    $notification->markAsRead();

    if ($articleId) {
        return redirect()->route('articles.show', $articleId);
    }
    return redirect()->route('articles.index');
}
```

В `routes/web.php`:
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notification.read');
});
```

### Стилизация
**Требование:** Добавить CSS для выпадающего списка уведомлений.

**Реализация:** В `public/style.css`:
```css
.notifications-dropdown {
    position: relative;
    display: inline-block;
}

.notifications-btn {
    background-color: var(--accent-color);
    padding: 0.8rem 1.5rem;
    border: none;
    cursor: pointer;
}

.notifications-list {
    display: none;
    position: absolute;
    background-color: var(--card-background);
    min-width: 300px;
    z-index: 1;
}

.notifications-dropdown:hover .notifications-list {
    display: block;
}

.notification-item {
    padding: 1.2rem 1.6rem;
    display: block;
    border-bottom: 1px solid var(--border-color);
}
```

---

## Работа 13: Кеширование

### Настройка драйвера кеша
**Требование:** Использовать database драйвер для кеша.

**Теория:** Кеш позволяет хранить часто используемые данные для быстрого доступа, снижая нагрузку на БД.

**Реализация:**

Создать таблицу кеша:
```bash
php artisan cache:table
php artisan migrate
```

В `.env`:
```env
CACHE_DRIVER=database
```

### Кеширование списка статей
**Требование:** Кешировать главную страницу с пагинацией.

**Реализация:** В `ArticleController@index`:
```php
use Illuminate\Support\Facades\Cache;

public function index(Request $request)
{
    $page = $request->get('page', 1);
    $cacheKey = 'articles_page_' . $page;

    $articles = Cache::remember($cacheKey, 3600, function () {
        return Article::orderBy('created_at', 'desc')->paginate(10);
    });

    return view('articles.index', compact('articles'));
}
```

### Кеширование статьи с комментариями
**Требование:** Кешировать страницу статьи навсегда (until cache flush).

**Реализация:** В `ArticleController@show`:
```php
public function show($id)
{
    $cacheKey = 'article_' . $id;

    $article = Cache::rememberForever($cacheKey, function () use ($id) {
        return Article::with('comments')->findOrFail($id);
    });

    return view('articles.show', compact('article'));
}
```

### Очистка кеша при создании
**Требование:** При создании статьи очищать кеш главной страницы.

**Реализация:** В `ArticleController@store`:
```php
$article = Article::create($validated);

Cache::forget('articles_page_1');
for ($i = 2; $i <= 10; $i++) {
    Cache::forget('articles_page_' . $i);
}
```

### Полная очистка при изменении
**Требование:** При update/delete полностью очищать весь кеш.

**Реализация:**
```php
// В методах update и destroy
Cache::flush();
```

---

## Работа 14: Планировщик задач (Task Scheduling)

### Создание модели для логирования просмотров
**Требование:** Сохранять URL просмотров статей в БД.

**Реализация:**
```bash
php artisan make:model ArticleView -m
```

В миграции:
```php
$table->id();
$table->string('url');
$table->timestamps();
```

В модели:
```php
protected $fillable = ['url'];
```

### Создание Middleware
**Требование:** Логировать каждый просмотр статьи.

**Реализация:**
```bash
php artisan make:middleware LogArticleViews
```

В `app/Http/Middleware/LogArticleViews.php`:
```php
use App\Models\ArticleView;

public function handle(Request $request, Closure $next)
{
    ArticleView::create([
        'url' => $request->fullUrl()
    ]);

    return $next($request);
}
```

### Регистрация Middleware
**Требование:** Зарегистрировать middleware и применить на роут статьи.

**Реализация:**

В `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    'log.article.views' => \App\Http\Middleware\LogArticleViews::class,
];
```

В `routes/web.php`:
```php
Route::get('/articles/{article}', [ArticleController::class, 'show'])
     ->name('articles.show')
     ->middleware('log.article.views');
```

### Создание команды для статистики
**Требование:** Создать команду для отправки ежедневной статистики модератору.

**Реализация:**
```bash
php artisan make:command SendDailyStatistics
```

В `app/Console/Commands/SendDailyStatistics.php`:
```php
protected $signature = 'statistics:daily';
protected $description = 'Send daily statistics to moderators';

public function handle()
{
    $today = now()->startOfDay();

    $viewsCount = ArticleView::where('created_at', '>=', $today)->count();
    $commentsCount = Comment::where('created_at', '>=', $today)->count();

    $moderatorEmail = env('MODERATOR_EMAIL');

    if ($moderatorEmail) {
        Mail::raw(
            "Статистика сайта за день:\n\nПросмотров статей: {$viewsCount}\nНовых комментариев: {$commentsCount}",
            function ($message) use ($moderatorEmail) {
                $message->to($moderatorEmail)
                    ->subject('Ежедневная статистика сайта');
            }
        );
        $this->info('Статистика отправлена модератору');
    }

    return 0;
}
```

### Настройка планировщика
**Требование:** Запускать команду каждую минуту (для тестирования).

**Реализация:** В `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('statistics:daily')->everyMinute();
}
```

### Запуск планировщика
**Требование:** Запустить Laravel Scheduler.

**Реализация:**
```bash
php artisan schedule:run
```

Для постоянной работы добавить в cron (на сервере):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Работа 15: Backend API (REST API)

### Создание API контроллеров
**Требование:** Создать API версии контроллеров для работы через JSON.

**Теория:** REST API позволяет работать с приложением через HTTP запросы, возвращая JSON вместо HTML.

**Реализация:**
```bash
php artisan make:controller API/ArticleController --api
php artisan make:controller API/AuthController
```

### ArticleController API
**Требование:** Реализовать CRUD операции через API.

**Реализация:** В `app/Http/Controllers/API/ArticleController.php`:
```php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $articles = Cache::remember('articles_page_' . $page, 3600, function () {
            return Article::orderBy('created_at', 'desc')->paginate(10);
        });
        return response()->json($articles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'author' => 'nullable|string|max:255',
        ]);

        $article = Article::create($validated);
        Cache::forget('articles_page_1');

        return response()->json(['success' => true, 'article' => $article], 201);
    }

    public function show($id)
    {
        $article = Cache::rememberForever('article_' . $id, function () use ($id) {
            return Article::with('comments')->findOrFail($id);
        });
        return response()->json($article);
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'author' => 'nullable|string|max:255',
        ]);
        $article->update($validated);
        Cache::flush();
        return response()->json(['success' => true, 'article' => $article]);
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();
        Cache::flush();
        return response()->json(['success' => true, 'message' => 'Статья удалена']);
    }
}
```

### AuthController API
**Требование:** Реализовать регистрацию, вход и выход через API с токенами.

**Реализация:** В `app/Http/Controllers/API/AuthController.php`:
```php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function registration(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Регистрация прошла успешно'
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($validated)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Неверные учетные данные'
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Выход выполнен успешно'
        ]);
    }
}
```

### API Routes
**Требование:** Зарегистрировать API роуты в routes/api.php.

**Реализация:** В `routes/api.php`:
```php
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\AuthController;

// Аутентификация
Route::post('/register', [AuthController::class, 'registration']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Статьи (публичные)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);

// Статьи (требуется авторизация)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);
});
```

### Тестирование API
**Требование:** Проверить работу API через Postman или Insomnia.

**Примеры запросов:**
```
GET http://localhost/api/articles
POST http://localhost/api/login
    Body: {"email": "test@example.com", "password": "123456"}
POST http://localhost/api/articles
    Headers: Authorization: Bearer {token}
    Body: {"title": "Test", "content": "Content here", "author": "Author"}
```

---

## Дополнительные команды

### Broadcasting
```bash
php artisan config:clear
npm run dev
npm run watch
npm run build
```

### Notifications
```bash
php artisan notifications:table
php artisan vendor:publish
```

### Cache
```bash
php artisan cache:table
php artisan cache:clear
php artisan config:cache
```

### Schedule
```bash
php artisan schedule:run
php artisan schedule:list
php artisan make:command Name
```

### API
```bash
php artisan route:list
php artisan route:list --path=api
```

---

## Проверочный список перед сдачей

### Базовая настройка
- [ ] Все миграции выполнены (`php artisan migrate:status`)
- [ ] Сиды выполнены (в БД есть роли и модератор)
- [ ] `.env` настроен корректно (MAIL, PUSHER, CACHE, QUEUE)
- [ ] Фронтенд собран (`npm run dev` или `npm run build`)

### Функционал работ 8-10
- [ ] Email рассылка работает (настроен SMTP)
- [ ] Модерация комментариев доступна только модератору
- [ ] Комментарии на модерации скрыты от обычных пользователей
- [ ] Очереди работают (`php artisan queue:work`)

### Функционал работ 11-12
- [ ] Broadcasting настроен (Pusher credentials в .env)
- [ ] Пуш-уведомления отображаются при создании статьи
- [ ] Database notifications работают (в БД таблица notifications)
- [ ] Панель уведомлений показывает непрочитанные уведомления

### Функционал работ 13-14
- [ ] Кеширование работает (CACHE_DRIVER=database)
- [ ] Кеш очищается при изменении статей
- [ ] Middleware LogArticleViews записывает просмотры
- [ ] Команда statistics:daily отправляет статистику
- [ ] Планировщик настроен (schedule в Kernel.php)

### Функционал работы 15
- [ ] API контроллеры в папке API/
- [ ] API роуты в routes/api.php
- [ ] API возвращает JSON (не HTML)
- [ ] Аутентификация через токены работает (Sanctum)

### Авторизация и политики
- [ ] Модератор может создавать/редактировать/удалять статьи
- [ ] Читатели могут только просматривать и комментировать
- [ ] Авторы комментариев могут их редактировать/удалять
- [ ] Gates и Policies настроены корректно

### Проверка в браузере
- [ ] Главная страница загружается
- [ ] Авторизация/регистрация работает
- [ ] Создание статьи модератором работает
- [ ] Комментарии добавляются и модерируются
- [ ] Уведомления отображаются
- [ ] Пуш-уведомления появляются (при настроенном Pusher)

---

## Часто встречающиеся проблемы

### Broadcasting не работает
- Проверьте credentials Pusher в .env
- Убедитесь, что BroadcastServiceProvider раскомментирован в config/app.php
- Выполните `npm run dev` для сборки фронтенда
- Проверьте консоль браузера на ошибки

### Email не отправляются
- Проверьте SMTP настройки в .env
- Убедитесь, что пароль - это пароль приложения (для mail.ru)
- Запустите `php artisan queue:work` для обработки заданий
- Проверьте таблицу jobs на наличие заданий

### Кеш не работает
- Выполните `php artisan cache:table` и `php artisan migrate`
- Проверьте CACHE_DRIVER=database в .env
- Проверьте таблицу cache в БД

### Планировщик не отправляет статистику
- Запустите `php artisan schedule:run` вручную
- Проверьте email модератора в .env (MODERATOR_EMAIL)
- Убедитесь, что в таблице article_views есть записи

### Ошибки при сборке фронтенда
- Удалите node_modules и выполните `npm install`
- Проверьте версии в package.json (Vue 3, Laravel Mix)
- Убедитесь, что webpack.mix.js содержит `.vue()`

---

# 🚀 Быстрый запуск проекта

## Минимальная настройка для запуска

### 1️⃣ Запустить MySQL в XAMPP
Откройте XAMPP Control Panel и запустите **MySQL**

### 2️⃣ Запустить сервер Laravel
```bash
php artisan serve
```

✅ Сайт доступен: **http://127.0.0.1:8000**

---

## 🎯 Тестовые учетные данные

### Модератор
- **Email:** moderator@example.com
- **Пароль:** password
- Может создавать статьи, модерировать комментарии

---

## 🔧 Дополнительные сервисы (опционально)

### Обработчик очереди (для email)
Откройте **новый терминал** и выполните:
```bash
php artisan queue:work
```

### Планировщик (для статистики)
```bash
php artisan schedule:run
```

---

## 📋 Полезные команды

```bash
# Очистить кеш
php artisan cache:clear
php artisan config:clear

# Пересоздать БД с тестовыми данными
php artisan migrate:fresh --seed

# Собрать фронтенд (если что-то изменили)
npm run dev
```

---

## ✅ Проверка работоспособности

1. Откройте http://127.0.0.1:8000
2. Войдите как модератор (moderator@example.com / password)
3. Создайте новую статью
4. Проверьте уведомления в навигации
5. Откройте "Модерация" для работы с комментариями

**Всё работает!** 🎉

---

# 📍 Где находятся данные проекта

## 🗄️ База данных MySQL

**Расположение:** `C:\xampp\mysql\data\laravel\`

**Доступ через phpMyAdmin:** http://localhost/phpmyadmin

### Таблицы в БД `laravel`:

#### 📦 Основные данные
| Таблица | Описание | Записей |
|---------|----------|---------|
| `articles` | Статьи блога | ~47 |
| `comments` | Комментарии к статьям | Переменное |
| `users` | Пользователи | 5 |
| `roles` | Роли (moderator, reader) | 2 |

#### 🔧 Системные таблицы
| Таблица | Описание | Что хранит |
|---------|----------|------------|
| **`cache`** | **Кеш приложения** | **Закешированные статьи и списки** |
| **`jobs`** | **Очередь заданий** | **Задания на отправку email** |
| **`notifications`** | **Уведомления** | **Уведомления для пользователей** |
| **`article_views`** | **Логи просмотров** | **URL просмотренных статей** |
| `failed_jobs` | Неудавшиеся задания | Ошибки очереди |
| `password_resets` | Сброс паролей | Токены сброса |
| `personal_access_tokens` | API токены | Токены Sanctum |

---

## 💾 Кеш (CACHE_DRIVER=database)

### Где находится:
- **Таблица БД:** `laravel.cache`
- **Путь к файлу БД:** `C:\xampp\mysql\data\laravel\cache.ibd`
- **Доступ:** phpMyAdmin → БД `laravel` → таблица `cache`

### Что кешируется:

**Текущие записи в кеше:**
```
✅ laravel_cachearticle_33        - Статья #33 (кеш навсегда)
✅ laravel_cachearticle_40        - Статья #40 (кеш навсегда)
✅ laravel_cachearticles_page_1   - Список статей стр.1 (1 час)
```

### Структура записи:
- `key` - ключ кеша (например: `laravel_cachearticles_page_1`)
- `value` - сериализованные данные (статьи в JSON)
- `expiration` - timestamp истечения

### Как просмотреть:
```bash
# Через скрипт
php check_cache.php

# Через phpMyAdmin
http://localhost/phpmyadmin
→ БД laravel → Таблица cache
```

---

## 📬 Очередь заданий (QUEUE_CONNECTION=database)

### Где находится:
- **Таблица БД:** `laravel.jobs`
- **Путь:** `C:\xampp\mysql\data\laravel\jobs.ibd`

### Что хранит:
- Задания на отправку email модератору
- Задания на отправку email пользователям
- Другие отложенные задания

### Как работает:
1. При создании статьи → задание добавляется в таблицу `jobs`
2. Worker обрабатывает задания: `php artisan queue:work`
3. После обработки → задание удаляется из таблицы

### Проверка:
```bash
# Посмотреть текущие задания
php artisan queue:failed

# Количество заданий в очереди
mysql -uroot -e "USE laravel; SELECT COUNT(*) FROM jobs;"
```

---

## 🔔 Уведомления (Database Notifications)

### Где находится:
- **Таблица БД:** `laravel.notifications`
- **Путь:** `C:\xampp\mysql\data\laravel\notifications.ibd`

### Что хранит:
- Уведомления о новых статьях для пользователей
- Статус прочтения (read_at)
- Данные уведомления в JSON формате

### Структура:
```
id              - UUID уведомления
type            - App\Notifications\NewArticleNotification
notifiable_type - App\Models\User
notifiable_id   - ID пользователя
data            - JSON с информацией о статье
read_at         - Время прочтения (NULL = не прочитано)
created_at      - Время создания
```

### Как просмотреть:
- **В интерфейсе:** Панель уведомлений в навигации (колокольчик)
- **В БД:** phpMyAdmin → `notifications`

---

## 📊 Логи просмотров статей

### Где находится:
- **Таблица БД:** `laravel.article_views`
- **Путь:** `C:\xampp\mysql\data\laravel\article_views.ibd`

### Что хранит:
- URL каждого просмотра статьи
- Timestamp просмотра

### Как используется:
- Middleware `LogArticleViews` записывает каждый просмотр
- Команда `statistics:daily` подсчитывает просмотры за день
- Отправляет статистику модератору

---

## 📁 Другие места хранения данных

### Логи приложения
**Расположение:** `storage/logs/laravel.log`
```bash
# Просмотр последних логов
tail -f storage/logs/laravel.log
```

### Сессии (если FILE driver)
**Расположение:** `storage/framework/sessions/`

### Загруженные файлы
**Расположение:** `storage/app/`

### Скомпилированные views
**Расположение:** `storage/framework/views/`

### Скомпилированный JS/CSS
**Расположение:**
- `public/js/app.js` (1.5 MB)
- `public/css/app.css`

---

## 🔍 Как проверить все данные

### 1. Через phpMyAdmin
```
http://localhost/phpmyadmin
→ База данных: laravel
→ Таблицы: cache, jobs, notifications, article_views, articles, comments, users
```

### 2. Через скрипты проверки
```bash
# Кеш
php check_cache.php

# Кеш (старый скрипт)
php test_cache.php
```

### 3. Через tinker (интерактивная консоль)
```bash
php artisan tinker
```

Примеры команд:
```php
// Кеш
DB::table('cache')->count();
DB::table('cache')->get();

// Очередь
DB::table('jobs')->count();

// Уведомления
DB::table('notifications')->count();
DB::table('notifications')->where('read_at', NULL)->count(); // непрочитанные

// Логи просмотров
DB::table('article_views')->count();
DB::table('article_views')->whereDate('created_at', today())->count(); // сегодня
```

---

## 📋 Итоговая таблица расположений

| Тип данных | Где хранится | Путь к файлу |
|------------|--------------|--------------|
| **Кеш** | БД `laravel.cache` | `C:\xampp\mysql\data\laravel\cache.ibd` |
| **Очередь** | БД `laravel.jobs` | `C:\xampp\mysql\data\laravel\jobs.ibd` |
| **Уведомления** | БД `laravel.notifications` | `C:\xampp\mysql\data\laravel\notifications.ibd` |
| **Логи просмотров** | БД `laravel.article_views` | `C:\xampp\mysql\data\laravel\article_views.ibd` |
| **Статьи** | БД `laravel.articles` | `C:\xampp\mysql\data\laravel\articles.ibd` |
| **Комментарии** | БД `laravel.comments` | `C:\xampp\mysql\data\laravel\comments.ibd` |
| **Пользователи** | БД `laravel.users` | `C:\xampp\mysql\data\laravel\users.ibd` |
| **Логи Laravel** | Файл | `storage/logs/laravel.log` |
| **Сессии** | Файлы | `storage/framework/sessions/` |

---

## 🛠 Полезные команды

```bash
# Очистить кеш
php artisan cache:clear

# Очистить очередь (осторожно!)
php artisan queue:flush

# Очистить логи
echo "" > storage/logs/laravel.log

# Посмотреть статус миграций
php artisan migrate:status

# Пересоздать БД с данными
php artisan migrate:fresh --seed
```

---

## ✅ Быстрая проверка всех данных

Выполните команду:
```bash
php check_cache.php
```

Результат покажет:
- ✅ Количество записей в кеше
- ✅ Ключи кеша
- ✅ Структуру таблицы
- ✅ Расположение БД
- ✅ Тест записи/чтения кеша

---

# Инструкция по настройке и запуску проекта

## Текущее состояние проекта

✅ База данных настроена и заполнена данными
✅ Миграции выполнены (14 таблиц)
✅ В БД есть роли, пользователи и статьи
✅ Фронтенд собран
✅ Зависимости установлены

## Быстрый запуск (если все уже настроено)

### 1. Запустить XAMPP
- Запустите MySQL (Apache опционально)

### 2. Запустить Laravel сервер
```bash
php artisan serve
```

Сайт будет доступен по адресу: **http://127.0.0.1:8000**

### 3. (Опционально) Запустить обработчик очереди
Для работы email-рассылки и уведомлений откройте новый терминал:
```bash
php artisan queue:work
```

### 4. (Опционально) Запустить планировщик
Для отправки ежедневной статистики:
```bash
php artisan schedule:run
```

---

## Полная настройка (если проект новый)

### Шаг 1: Проверка окружения

**Требования:**
- PHP >= 8.0 ✅ (у вас 8.0.30)
- Composer ✅ (у вас 2.9.2)
- Node.js + NPM ✅ (у вас 11.2.0)
- MySQL (через XAMPP) ✅

### Шаг 2: Установка зависимостей

```bash
# PHP зависимости
composer install

# JavaScript зависимости
npm install
```

### Шаг 3: Настройка .env

Ваш файл `.env` уже настроен! Проверьте основные параметры:

**База данных:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

**Email (уже настроен на Gmail):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ilyatelik@gmail.com
MAIL_PASSWORD=pgmtueollsswqthf
MAIL_ENCRYPTION=tls
MODERATOR_EMAIL=ilyatelik@gmail.com
```

**Pusher (для пуш-уведомлений):**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=1925099
PUSHER_APP_KEY=8c6a9df8fe2eeb1e74f2
PUSHER_APP_SECRET=60f10a40fa89f88e7c42
PUSHER_APP_CLUSTER=eu
```

**Кеш и очереди:**
```env
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

### Шаг 4: Создание и заполнение БД

Если БД пустая или нужно пересоздать:

```bash
# Создать БД в MySQL (если еще не создана)
# В phpMyAdmin или через командную строку создайте БД с именем "laravel"

# Выполнить миграции и сиды
php artisan migrate:fresh --seed
```

Это создаст:
- Таблицы БД
- 2 роли (moderator, reader)
- Пользователя-модератора (email: moderator@example.com, пароль: password)
- 20 тестовых статей

### Шаг 5: Сборка фронтенда

```bash
# Для разработки
npm run dev

# Или для отслеживания изменений
npm run watch

# Для production
npm run prod
```

### Шаг 6: Очистка кеша

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Тестовые учетные данные

### Модератор (создается автоматически)
- **Email:** moderator@example.com
- **Пароль:** password
- **Права:** может создавать/редактировать/удалять статьи, модерировать комментарии

### Создать нового читателя
Зарегистрируйтесь через форму регистрации на сайте или выполните:

```bash
php artisan tinker
```

В tinker:
```php
$user = new App\Models\User();
$user->name = 'Читатель';
$user->email = 'reader@example.com';
$user->password = Hash::make('password');
$user->role_id = 2; // reader role
$user->save();
```

---

## Проверка работоспособности

### 1. Веб-интерфейс
Откройте в браузере: **http://127.0.0.1:8000**

**Должны работать:**
- ✅ Главная страница со списком статей
- ✅ Регистрация и авторизация
- ✅ Создание статьи (для модератора)
- ✅ Комментарии к статьям
- ✅ Модерация комментариев (для модератора)
- ✅ Уведомления в навигационной панели

### 2. API
Проверить через Postman или curl:

```bash
# Получить список статей
curl http://127.0.0.1:8000/api/articles

# Авторизация
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"moderator@example.com","password":"password"}'
```

### 3. Email-рассылка
1. Запустите обработчик очереди: `php artisan queue:work`
2. Создайте новую статью от имени модератора
3. Проверьте таблицу `jobs` в БД - должно появиться задание
4. Задание будет обработано, и на email модератора придет письмо

### 4. Broadcasting (пуш-уведомления)
1. Откройте сайт в браузере
2. Откройте консоль разработчика (F12)
3. Создайте новую статью
4. В верхней части страницы должно появиться всплывающее уведомление
5. В консоли должны появиться логи от Echo/Pusher

### 5. Кеширование
Проверить работу кеша:
```bash
php test_cache.php
```

Или через tinker:
```bash
php artisan tinker
```
```php
Cache::put('test', 'value', 3600);
Cache::get('test'); // должно вернуть 'value'
```

### 6. Планировщик
```bash
php artisan schedule:run
```

Проверьте почту модератора - должна прийти статистика за день.

---

## Команды для разработки

### Работа с БД
```bash
php artisan migrate              # Выполнить миграции
php artisan migrate:fresh        # Пересоздать БД
php artisan migrate:fresh --seed # Пересоздать БД и заполнить данными
php artisan db:seed              # Выполнить сиды
php artisan tinker               # Открыть интерактивную консоль
```

### Очереди
```bash
php artisan queue:work           # Запустить обработчик очереди
php artisan queue:listen         # То же, но с автоперезагрузкой
php artisan queue:restart        # Перезапустить воркеры
php artisan queue:failed         # Список неудавшихся заданий
```

### Кеш
```bash
php artisan cache:clear          # Очистить кеш
php artisan config:clear         # Очистить кеш конфигурации
php artisan view:clear           # Очистить кеш представлений
php artisan route:clear          # Очистить кеш маршрутов
```

### Фронтенд
```bash
npm run dev                      # Собрать для разработки
npm run watch                    # Отслеживать изменения
npm run prod                     # Собрать для production
```

### Другие
```bash
php artisan route:list           # Список всех маршрутов
php artisan route:list --path=api # Только API маршруты
php artisan schedule:list        # Список запланированных задач
```

---

## Часто встречающиеся проблемы

### ❌ Ошибка подключения к БД
**Решение:**
1. Убедитесь, что MySQL запущен в XAMPP
2. Проверьте настройки БД в `.env`
3. Создайте БД `laravel` в phpMyAdmin, если её нет

### ❌ 404 Not Found
**Решение:**
```bash
php artisan route:clear
php artisan config:clear
```

### ❌ Class not found
**Решение:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

### ❌ CSRF token mismatch
**Решение:**
1. Очистите кеш браузера
2. Выполните: `php artisan config:clear`
3. Перезапустите сервер

### ❌ Mix manifest not found
**Решение:**
```bash
npm run dev
```

### ❌ Email не отправляются
**Решение:**
1. Проверьте настройки SMTP в `.env`
2. Для Gmail используйте "пароль приложения", а не основной пароль
3. Запустите обработчик очереди: `php artisan queue:work`

### ❌ Пуш-уведомления не работают
**Решение:**
1. Проверьте credentials Pusher в `.env`
2. Убедитесь, что фронтенд собран: `npm run dev`
3. Откройте консоль браузера и проверьте ошибки
4. Убедитесь, что `BroadcastServiceProvider` раскомментирован в `config/app.php`

---

## Структура URL

### Веб-интерфейс
- `http://127.0.0.1:8000/` - Главная страница (JSON данные)
- `http://127.0.0.1:8000/articles` - Список статей
- `http://127.0.0.1:8000/articles/{id}` - Просмотр статьи
- `http://127.0.0.1:8000/articles/create` - Создание статьи (только модератор)
- `http://127.0.0.1:8000/comments/moderation` - Модерация комментариев (только модератор)
- `http://127.0.0.1:8000/login` - Вход
- `http://127.0.0.1:8000/signin` - Регистрация

### API
- `GET /api/articles` - Список статей
- `GET /api/articles/{id}` - Просмотр статьи
- `POST /api/login` - Вход (получить токен)
- `POST /api/register` - Регистрация
- `POST /api/articles` - Создать статью (требуется токен)
- `PUT /api/articles/{id}` - Обновить статью (требуется токен)
- `DELETE /api/articles/{id}` - Удалить статью (требуется токен)

---

## Дополнительная информация

### Где находятся данные

**Кеш:** таблица `cache` в БД MySQL
**Очередь:** таблица `jobs` в БД MySQL
**Уведомления:** таблица `notifications` в БД MySQL
**Логи просмотров:** таблица `article_views` в БД MySQL

### Файлы конфигурации

- `.env` - переменные окружения
- `config/app.php` - основная конфигурация
- `config/database.php` - настройки БД
- `config/mail.php` - настройки почты
- `config/broadcasting.php` - настройки Broadcasting
- `config/cache.php` - настройки кеша
- `config/queue.php` - настройки очереди

### Логи

Все логи приложения находятся в `storage/logs/laravel.log`

Для просмотра последних логов:
```bash
tail -f storage/logs/laravel.log
```

---

## Готово к работе!

Проект полностью настроен и готов к использованию. Все 15 лабораторных работ реализованы:

1. ✅ Работы 1-7 (уже сданы)
2. ✅ Работа 8 - Email рассылка
3. ✅ Работа 9 - Модерация комментариев
4. ✅ Работа 10 - Очереди Laravel
5. ✅ Работа 11 - Пуш-уведомления (Broadcasting)
6. ✅ Работа 12 - Database Notifications
7. ✅ Работа 13 - Кеширование
8. ✅ Работа 14 - Планировщик задач
9. ✅ Работа 15 - Backend API

---

# 🧪 Мини-тесты для проверки работы всех функций

## Тест 1: Email рассылка (Работа 8)

**Что проверяем:** При создании статьи модератору приходит email с информацией о новой статье.

**Шаги:**
1. Запустите обработчик очереди в отдельном терминале:
   ```bash
   php artisan queue:work
   ```
2. Войдите на сайт как модератор (moderator@example.com / password)
3. Перейдите в "Создать статью" (http://127.0.0.1:8000/articles/create)
4. Заполните форму:
   - **Заголовок:** Тестовая статья для проверки email
   - **Автор:** Тестовый автор
   - **Содержание:** Это тестовая статья для проверки отправки email уведомлений модератору
5. Нажмите "Создать"

**Ожидаемый результат:**
- ✅ Статья создана успешно
- ✅ В терминале с queue:work появилось сообщение о обработке задания
- ✅ Проверьте таблицу `jobs` в phpMyAdmin - задание было обработано и удалено
- ✅ На email модератора (ilyatelik@gmail.com) пришло письмо с темой "Новая статья: Тестовая статья для проверки email"

**Как проверить в БД:**
```bash
php artisan tinker
```
```php
DB::table('jobs')->count(); // Должно быть 0 если задание обработано
```

---

## Тест 2: Модерация комментариев (Работа 9)

**Что проверяем:** Комментарии требуют одобрения модератором перед отображением.

**Шаги:**

### Часть 1: Создание комментария читателем
1. Зарегистрируйте нового пользователя или войдите как обычный пользователь (не модератор)
2. Откройте любую статью
3. Добавьте комментарий: "Это тестовый комментарий для проверки модерации"
4. Нажмите "Добавить комментарий"

**Ожидаемый результат:**
- ✅ Сообщение "Комментарий отправлен на модерацию"
- ✅ Комментарий НЕ отображается на странице статьи (потому что не одобрен)

### Часть 2: Модерация комментария
1. Выйдите из текущего аккаунта
2. Войдите как модератор (moderator@example.com / password)
3. Перейдите в "Модерация комментариев" (http://127.0.0.1:8000/comments/moderation)
4. Вы увидите список комментариев на модерации
5. Нажмите "Одобрить" для вашего комментария

**Ожидаемый результат:**
- ✅ Комментарий исчез из списка модерации
- ✅ Откройте статью - комментарий теперь отображается

**Как проверить в БД:**
```bash
php artisan tinker
```
```php
// Комментарии на модерации
DB::table('comments')->where('is_approved', false)->count();

// Одобренные комментарии
DB::table('comments')->where('is_approved', true)->count();
```

---

## Тест 3: Очереди Laravel (Работа 10)

**Что проверяем:** Email отправляется через очередь, а не синхронно.

**Шаги:**
1. Убедитесь, что `QUEUE_CONNECTION=database` в .env
2. Остановите обработчик очереди (если запущен)
3. Создайте новую статью как модератор
4. Проверьте таблицу `jobs` в phpMyAdmin

**Ожидаемый результат:**
- ✅ В таблице `jobs` появилась новая запись (задание на отправку email)
- ✅ Задание содержит сериализованные данные статьи

**Шаги 2:**
1. Запустите обработчик очереди:
   ```bash
   php artisan queue:work
   ```
2. Понаблюдайте за выводом в терминале

**Ожидаемый результат:**
- ✅ Задание обработано
- ✅ Email отправлен
- ✅ Запись из таблицы `jobs` удалена

**Как проверить в БД:**
```bash
php artisan tinker
```
```php
// Количество заданий в очереди
DB::table('jobs')->count();

// Посмотреть все задания
DB::table('jobs')->get();
```

---

## Тест 4: Пуш-уведомления Broadcasting (Работа 11)

**Что проверяем:** При создании статьи всплывает уведомление в реальном времени.

**Подготовка:**
1. Убедитесь, что в .env указаны правильные Pusher credentials:
   ```
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=1925099
   PUSHER_APP_KEY=8c6a9df8fe2eeb1e74f2
   PUSHER_APP_SECRET=60f10a40fa89f88e7c42
   PUSHER_APP_CLUSTER=eu
   ```
2. Убедитесь, что фронтенд собран: `npm run dev`

**Шаги:**
1. Откройте сайт в браузере (http://127.0.0.1:8000)
2. Откройте консоль разработчика (F12) → вкладка Console
3. В консоли должны быть логи от Pusher (подключение к каналу)
4. Откройте второе окно браузера
5. Войдите как модератор во втором окне
6. Создайте новую статью во втором окне

**Ожидаемый результат в первом окне:**
- ✅ В консоли появилось событие NewArticleEvent с данными статьи
- ✅ В верхней части страницы появилось всплывающее уведомление: "Добавлена новая статья [название]"
- ✅ При клике на название статьи происходит переход на страницу статьи

**Проверка в консоли браузера:**
Вы должны увидеть что-то вроде:
```
Pusher: Event recd { event: 'NewArticleEvent', channel: 'test', data: {...} }
```

---

## Тест 5: Database Notifications (Работа 12)

**Что проверяем:** Уведомления сохраняются в БД и отображаются в панели уведомлений.

**Шаги:**
1. Войдите как обычный пользователь (не модератор)
2. Посмотрите на навигационную панель - должна быть кнопка "Уведомления"
3. В другой вкладке браузера войдите как модератор
4. Создайте новую статью как модератор
5. Вернитесь в первую вкладку с обычным пользователем
6. Обновите страницу (F5)

**Ожидаемый результат:**
- ✅ На кнопке "Уведомления" появился бейдж с количеством непрочитанных (например, "1")
- ✅ При наведении на кнопку открывается выпадающий список
- ✅ В списке отображается уведомление: "Добавлена новая статья: [название]"
- ✅ При клике на уведомление открывается страница статьи
- ✅ После клика бейдж исчезает (уведомление помечено как прочитанное)

**Как проверить в БД:**
```bash
php artisan tinker
```
```php
// Все уведомления
DB::table('notifications')->count();

// Непрочитанные уведомления
DB::table('notifications')->whereNull('read_at')->count();

// Прочитанные уведомления
DB::table('notifications')->whereNotNull('read_at')->count();

// Посмотреть последнее уведомление
DB::table('notifications')->latest()->first();
```

---

## Тест 6: Кеширование (Работа 13)

**Что проверяем:** Список статей и отдельные статьи кешируются в БД.

**Шаги:**

### Часть 1: Проверка кеширования списка статей
1. Очистите кеш:
   ```bash
   php artisan cache:clear
   ```
2. Откройте главную страницу со списком статей (http://127.0.0.1:8000/articles)
3. Проверьте таблицу `cache` в phpMyAdmin

**Ожидаемый результат:**
- ✅ В таблице `cache` появилась запись с ключом `laravel_cachearticles_page_1`
- ✅ Поле `expiration` содержит timestamp (через 1 час от текущего времени)

### Часть 2: Проверка кеширования отдельной статьи
1. Откройте любую статью (например, http://127.0.0.1:8000/articles/1)
2. Проверьте таблицу `cache` в phpMyAdmin

**Ожидаемый результат:**
- ✅ В таблице `cache` появилась запись с ключом `laravel_cachearticle_1`
- ✅ Поле `expiration` содержит очень большое число (кеш навсегда)

### Часть 3: Проверка очистки кеша при создании статьи
1. Создайте новую статью как модератор
2. Проверьте таблицу `cache` в phpMyAdmin

**Ожидаемый результат:**
- ✅ Запись `laravel_cachearticles_page_1` удалена из таблицы
- ✅ Старые статьи остались в кеше

**Как проверить через скрипт:**
```bash
php check_cache.php
```

**Как проверить в БД:**
```bash
php artisan tinker
```
```php
// Количество записей в кеше
DB::table('cache')->count();

// Посмотреть все ключи кеша
DB::table('cache')->pluck('key');

// Посмотреть конкретный кеш
Cache::get('articles_page_1');
```

---

## Тест 7: Планировщик задач (Работа 14)

**Что проверяем:** Логируются просмотры статей и отправляется статистика модератору.

**Шаги:**

### Часть 1: Проверка логирования просмотров
1. Откройте несколько статей (например, 3-5 штук)
2. Проверьте таблицу `article_views` в phpMyAdmin

**Ожидаемый результат:**
- ✅ В таблице `article_views` появились новые записи
- ✅ Каждая запись содержит URL просмотренной статьи
- ✅ Каждая запись содержит timestamp просмотра

**Как проверить в БД:**
```bash
php artisan tinker
```
```php
// Общее количество просмотров
DB::table('article_views')->count();

// Просмотры за сегодня
DB::table('article_views')->whereDate('created_at', today())->count();

// Последние 5 просмотров
DB::table('article_views')->latest()->limit(5)->get();
```

### Часть 2: Проверка отправки статистики
1. Добавьте несколько комментариев к статьям
2. Запустите команду отправки статистики:
   ```bash
   php artisan statistics:daily
   ```

**Ожидаемый результат:**
- ✅ В консоли появилось сообщение "Статистика отправлена модератору"
- ✅ На email модератора пришло письмо с темой "Ежедневная статистика сайта"
- ✅ В письме указано количество просмотров и комментариев за сегодня

### Часть 3: Проверка планировщика
1. Проверьте, что команда зарегистрирована в планировщике:
   ```bash
   php artisan schedule:list
   ```

**Ожидаемый результат:**
- ✅ В списке есть команда `statistics:daily`
- ✅ Указана частота выполнения (каждую минуту для тестирования)

---

## Тест 8: Backend API (Работа 15)

**Что проверяем:** REST API работает корректно через JSON.

**Шаги:**

### Тест 8.1: Получение списка статей (GET /api/articles)
Откройте в браузере или используйте curl:
```bash
curl http://127.0.0.1:8000/api/articles
```

**Ожидаемый результат:**
- ✅ Возвращается JSON с массивом статей
- ✅ Каждая статья содержит id, title, content, author, created_at, updated_at
- ✅ Присутствует пагинация (current_page, last_page, per_page и т.д.)

### Тест 8.2: Получение отдельной статьи (GET /api/articles/{id})
```bash
curl http://127.0.0.1:8000/api/articles/1
```

**Ожидаемый результат:**
- ✅ Возвращается JSON с данными статьи
- ✅ Присутствует массив comments (комментарии к статье)

### Тест 8.3: Регистрация через API (POST /api/register)
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"API User","email":"apiuser@test.com","password":"password123"}'
```

**Ожидаемый результат:**
- ✅ HTTP статус 201 Created
- ✅ JSON ответ: `{"success":true,"user":{...},"message":"Регистрация прошла успешно"}`
- ✅ В БД создан новый пользователь

### Тест 8.4: Вход через API (POST /api/login)
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"moderator@example.com","password":"password"}'
```

**Ожидаемый результат:**
- ✅ HTTP статус 200 OK
- ✅ JSON ответ: `{"success":true,"user":{...},"token":"..."}`
- ✅ Получен токен доступа (длинная строка)

### Тест 8.5: Создание статьи через API с токеном (POST /api/articles)
```bash
# Замените YOUR_TOKEN на токен из предыдущего теста
curl -X POST http://127.0.0.1:8000/api/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"title":"Статья через API","content":"Это содержание статьи созданной через API","author":"API Автор"}'
```

**Ожидаемый результат:**
- ✅ HTTP статус 201 Created
- ✅ JSON ответ: `{"success":true,"article":{...}}`
- ✅ Статья создана в БД
- ✅ Отправлены email и уведомления (так же как при создании через веб-интерфейс)

### Тест 8.6: Попытка создать статью БЕЗ токена
```bash
curl -X POST http://127.0.0.1:8000/api/articles \
  -H "Content-Type: application/json" \
  -d '{"title":"Статья без токена","content":"Контент","author":"Автор"}'
```

**Ожидаемый результат:**
- ✅ HTTP статус 401 Unauthorized
- ✅ Ошибка авторизации

### Тест 8.7: Выход через API (POST /api/logout)
```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Ожидаемый результат:**
- ✅ HTTP статус 200 OK
- ✅ JSON ответ: `{"success":true,"message":"Выход выполнен успешно"}`
- ✅ Токен удален из БД (не работает для последующих запросов)

---

## Комплексный тест всех функций

**Сценарий:** Полный жизненный цикл статьи с проверкой всех функций.

**Шаги:**

1. **Запустите все необходимые сервисы:**
   ```bash
   # Терминал 1: Laravel сервер
   php artisan serve

   # Терминал 2: Обработчик очереди
   php artisan queue:work
   ```

2. **Очистите кеш и БД тестовых данных:**
   ```bash
   php artisan cache:clear
   # Опционально: очистите таблицу article_views для чистой статистики
   ```

3. **Откройте два окна браузера:**
   - Окно A: войдите как модератор (moderator@example.com / password)
   - Окно B: войдите как обычный пользователь (или зарегистрируйте нового)

4. **В окне B (пользователь):**
   - Откройте консоль разработчика (F12)
   - Посмотрите на панель уведомлений (должно быть 0 или несколько старых)

5. **В окне A (модератор):**
   - Создайте новую статью с заголовком "Комплексный тест всех функций"
   - После создания вернитесь на главную страницу

6. **Проверьте результаты:**

   ✅ **Работа 8 (Email):**
   - В терминале с `queue:work` появилась обработка задания
   - Проверьте email модератора - пришло письмо

   ✅ **Работа 10 (Очереди):**
   - В phpMyAdmin таблица `jobs` была заполнена, потом очищена

   ✅ **Работа 11 (Broadcasting):**
   - В окне B в консоли появилось событие NewArticleEvent
   - Всплыло уведомление вверху страницы

   ✅ **Работа 12 (Database Notifications):**
   - В окне B обновите страницу
   - На кнопке "Уведомления" появился бейдж с цифрой
   - При наведении видно уведомление о новой статье

   ✅ **Работа 13 (Кеш):**
   - Проверьте таблицу `cache` - кеш главной страницы был очищен
   - Откройте созданную статью - появится кеш для неё

7. **В окне B (пользователь):**
   - Откройте созданную статью
   - Добавьте комментарий "Тестовый комментарий для модерации"

8. **Проверьте модерацию:**

   ✅ **Работа 9 (Модерация):**
   - Комментарий не отображается на странице статьи
   - В окне A (модератор) перейдите в "Модерация комментариев"
   - Одобрите комментарий
   - В окне B обновите страницу - комментарий теперь виден

9. **Проверьте логирование:**

   ✅ **Работа 14 (Планировщик):**
   - Проверьте таблицу `article_views` - записаны просмотры статьи
   - Выполните: `php artisan statistics:daily`
   - Проверьте email модератора - пришла статистика

10. **Проверьте API:**

    ✅ **Работа 15 (API):**
    ```bash
    # Получите список статей
    curl http://127.0.0.1:8000/api/articles

    # Войдите через API
    curl -X POST http://127.0.0.1:8000/api/login \
      -H "Content-Type: application/json" \
      -d '{"email":"moderator@example.com","password":"password"}'
    ```

**Ожидаемый итоговый результат:**
- ✅ Все 9 работ (8-15 и модерация из 9) работают корректно
- ✅ Email отправлены
- ✅ Уведомления в БД созданы
- ✅ Пуш-уведомления работают
- ✅ Комментарии проходят модерацию
- ✅ Кеш работает и очищается
- ✅ Просмотры логируются
- ✅ Статистика отправляется
- ✅ API возвращает корректный JSON

---

## Проверка таблиц БД

Все данные должны храниться в следующих таблицах:

```bash
php artisan tinker
```

```php
// Проверка всех ключевых таблиц
echo "Статьи: " . DB::table('articles')->count() . "\n";
echo "Комментарии: " . DB::table('comments')->count() . "\n";
echo "Пользователи: " . DB::table('users')->count() . "\n";
echo "Кеш: " . DB::table('cache')->count() . "\n";
echo "Очередь: " . DB::table('jobs')->count() . "\n";
echo "Уведомления: " . DB::table('notifications')->count() . "\n";
echo "Просмотры: " . DB::table('article_views')->count() . "\n";

// Непрочитанные уведомления
echo "Непрочитанные уведомления: " . DB::table('notifications')->whereNull('read_at')->count() . "\n";

// Комментарии на модерации
echo "Комментарии на модерации: " . DB::table('comments')->where('is_approved', false)->count() . "\n";
```

**Ожидаемый вывод:**
```
Статьи: 47+ (или больше в зависимости от количества созданных)
Комментарии: 10+ (в зависимости от тестов)
Пользователи: 5+ (в зависимости от регистраций)
Кеш: 3-10 (в зависимости от просмотров)
Очередь: 0 (если queue:work работает)
Уведомления: 50+ (по количеству статей × пользователей)
Просмотры: 20+ (в зависимости от просмотров)
Непрочитанные уведомления: варьируется
Комментарии на модерации: 0 (если все одобрены)
```

---

## Быстрая диагностика проблем

Если что-то не работает, выполните эту проверку:

```bash
# 1. Проверка конфигурации
php artisan config:clear
php artisan cache:clear

# 2. Проверка миграций
php artisan migrate:status

# 3. Проверка очередей
php artisan queue:failed

# 4. Проверка планировщика
php artisan schedule:list

# 5. Проверка роутов
php artisan route:list --path=api

# 6. Проверка кеша
php check_cache.php

# 7. Проверка логов
tail -100 storage/logs/laravel.log
```

**Ожидаемые результаты:**
- ✅ Все миграции выполнены (Ran status)
- ✅ Нет ошибок в очереди
- ✅ Команда `statistics:daily` в списке планировщика
- ✅ Все API роуты присутствуют
- ✅ Кеш работает
- ✅ В логах нет критических ошибок (CRITICAL, ERROR)
