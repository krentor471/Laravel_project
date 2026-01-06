<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Article;
use App\Models\User;
use App\Mail\NewCommentNotification;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    // Создание комментария
    public function store(Request $request, $articleId)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:3',
        ]);

        $article = Article::findOrFail($articleId);

        $validated['user_id'] = auth()->id();
        $comment = $article->comments()->create($validated);

        // Отправка уведомления модераторам
        $this->sendNotificationToModerators($comment);

        return redirect()->route('articles.show', $articleId)->with('success', 'Комментарий отправлен на модерацию');
    }

    /**
     * Отправка уведомления всем модераторам
     */
    private function sendNotificationToModerators(Comment $comment)
{
    \Log::info('=== НАЧАЛО sendNotificationToModerators ===');
    
    // Получаем всех модераторов
    $moderators = User::whereHas('role', function($query) {
        $query->where('name', 'moderator');
    })->get();
    
    \Log::info('Найдено модераторов:', ['count' => $moderators->count()]);
    
    if ($moderators->isEmpty()) {
        \Log::warning('Нет модераторов для отправки!');
        
        // Отправляем на fallback email
        Mail::to('karav1902@mail.ru')
             ->send(new NewCommentNotification($comment));
        return;
    }
    
    // Отправляем каждому модератору
    foreach ($moderators as $moderator) {
        try {
            \Log::info('Отправляю письмо модератору:', [
                'id' => $moderator->id,
                'email' => $moderator->email
            ]);
            
            Mail::to($moderator->email)
                 ->send(new NewCommentNotification($comment));
                 
            \Log::info('✅ Письмо отправлено на ' . $moderator->email);
        } catch (\Exception $e) {
            \Log::error('❌ Ошибка отправки на ' . $moderator->email . ': ' . $e->getMessage());
        }
    }
}

    // ========== ДОБАВЬТЕ ЭТИ МЕТОДЫ ==========

    // Редактирование комментария
    public function edit($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update-comment', $comment);
        return view('comments.edit', compact('comment'));
    }

    // Обновление комментария
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update-comment', $comment);

        $validated = $request->validate([
            'content' => 'required|string|min:3',
        ]);

        $comment->update($validated);

        return redirect()->route('articles.show', $comment->article_id)->with('success', 'Комментарий обновлен');
    }

    // Удаление комментария
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete-comment', $comment);
        $articleId = $comment->article_id;
        $comment->delete();

        return redirect()->route('articles.show', $articleId)->with('success', 'Комментарий удален');
    }

    // === ВАЖНО! Этого метода нет в вашем коде ===
    // Список комментариев для модерации
    public function moderation()
    {
        // Если у вас есть поле is_approved в таблице comments
        $comments = Comment::where('is_approved', false)
                           ->with(['article', 'user'])
                           ->orderBy('created_at', 'desc')
                           ->get();
        
        // Если поля is_approved нет, показываем все комментарии
        // $comments = Comment::with(['article', 'user'])
        //                    ->orderBy('created_at', 'desc')
        //                    ->get();
        
        return view('comments.moderation', compact('comments'));
    }

    // Одобрение комментария
    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Если есть поле is_approved
        $comment->update(['is_approved' => true]);
        
        // Если поля нет, просто удаляем из списка модерации
        // $comment->delete(); // или ничего не делаем
        
        return redirect()->route('comments.moderation')->with('success', 'Комментарий одобрен и опубликован');
    }

    // Отклонение комментария
    public function reject($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Можно либо удалить, либо пометить как отклонённый
        $comment->delete(); // или $comment->update(['is_rejected' => true]);
        
        return redirect()->route('comments.moderation')
                        ->with('success', 'Комментарий отклонён и удалён');
    }
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isModerator()) {
                abort(403, 'Доступ только для модераторов');
            }
            return $next($request);
        })->only(['moderation', 'approve', 'reject']);
    }
}