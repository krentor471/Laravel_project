<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Jobs\SendNewArticleNotification;
use App\Events\NewArticleEvent;
use App\Notifications\NewArticleNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = 'articles_page_' . $page;

        $articles = Cache::remember($cacheKey, 3600, function () {
            return Article::orderBy('created_at', 'desc')->paginate(10);
        });

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        $this->authorize('create', Article::class);
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Article::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'author' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Заголовок обязателен',
            'content.required' => 'Содержание обязательно',
            'content.min' => 'Содержание должно быть минимум 10 символов',
        ]);

        $article = Article::create($validated);

        // Отправка уведомления модератору через очередь
        try {
            SendNewArticleNotification::dispatch($article);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем создание статьи
            \Log::error('Ошибка отправки email: ' . $e->getMessage());
        }

        // Отправка пуш-уведомления
        event(new NewArticleEvent($article));

        // Отправка уведомлений читателям (всем пользователям)
        $readers = User::all();
        Notification::send($readers, new NewArticleNotification($article));

        // Очистка кеша главной страницы и всех страниц пагинации
        Cache::forget('articles_page_1');
        for ($i = 2; $i <= 10; $i++) {
            Cache::forget('articles_page_' . $i);
        }

        return redirect()->route('articles.index')->with('success', 'Статья создана');
    }

    public function show($id)
    {
        $cacheKey = 'article_' . $id;

        $article = Cache::rememberForever($cacheKey, function () use ($id) {
            return Article::with('comments')->findOrFail($id);
        });

        return view('articles.show', compact('article'));
    }

    public function edit($id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('update', $article);
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('update', $article);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'author' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Заголовок обязателен',
            'content.required' => 'Содержание обязательно',
            'content.min' => 'Содержание должно быть минимум 10 символов',
        ]);

        $article->update($validated);

        // Очистка всего кеша
        Cache::flush();

        return redirect()->route('articles.index')->with('success', 'Статья обновлена');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $this->authorize('delete', $article);
        $article->delete();

        // Очистка всего кеша
        Cache::flush();

        return redirect()->route('articles.index')->with('success', 'Статья удалена');
    }
}
