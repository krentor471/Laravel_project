<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = 'articles_page_' . $page;

        $articles = Cache::remember($cacheKey, 3600, function () {
            return Article::orderBy('created_at', 'desc')->paginate(10);
        });

        return response()->json($articles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'author' => 'nullable|string|max:255',
        ]);

        $article = Article::create($validated);

        // Отправка уведомления модератору через очередь
        try {
            SendNewArticleNotification::dispatch($article);
        } catch (\Exception $e) {
            \Log::error('Ошибка отправки email: ' . $e->getMessage());
        }

        // Отправка пуш-уведомления
        event(new NewArticleEvent($article));

        // Отправка уведомлений читателям
        $readers = User::all();
        Notification::send($readers, new NewArticleNotification($article));

        // Очистка кеша
        Cache::forget('articles_page_1');
        for ($i = 2; $i <= 10; $i++) {
            Cache::forget('articles_page_' . $i);
        }

        return response()->json(['success' => true, 'article' => $article], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cacheKey = 'article_' . $id;

        $article = Cache::rememberForever($cacheKey, function () use ($id) {
            return Article::with('comments')->findOrFail($id);
        });

        return response()->json($article);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'author' => 'nullable|string|max:255',
        ]);

        $article->update($validated);

        // Очистка всего кеша
        Cache::flush();

        return response()->json(['success' => true, 'article' => $article]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        // Очистка всего кеша
        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Статья удалена']);
    }
}
