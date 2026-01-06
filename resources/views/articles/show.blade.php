@extends('layouts.app')

@section('title', $article->title)

@section('content')
<article class="article-single">
    <h1>{{ $article->title }}</h1>

    <div class="article-meta">
        <span>Автор: {{ $article->author ?? 'Неизвестен' }}</span>
        <span>{{ $article->created_at->format('d.m.Y H:i') }}</span>
    </div>

    <div class="article-content">
        {!! nl2br(e($article->content)) !!}
    </div>

    <div class="article-actions">
        <a href="{{ route('articles.index') }}" class="btn-secondary">← Назад к списку</a>
        @can('update', $article)
            <a href="{{ route('articles.edit', $article->id) }}" class="btn">Редактировать</a>
        @endcan
        @can('delete', $article)
            <form action="{{ route('articles.destroy', $article->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete" onclick="return confirm('Удалить статью?')">Удалить</button>
            </form>
        @endcan
    </div>

    <!-- Комментарии -->
    <section class="comments-section">
        <h2>Комментарии</h2>

        @php
            $approvedComments = $article->comments->where('is_approved', true);
        @endphp

        @if($approvedComments->count() > 0)
            @foreach($approvedComments as $comment)
                <div class="comment">
                    <p>{{ $comment->content }}</p>
                    <small>
                        {{ $comment->user ? $comment->user->name : 'Аноним' }} | {{ $comment->created_at->format('d.m.Y H:i') }}
                    </small>
                    @can('update-comment', $comment)
                        <a href="{{ route('comments.edit', $comment->id) }}">Редактировать</a>
                    @endcan
                    @can('delete-comment', $comment)
                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Удалить комментарий?')">Удалить</button>
                        </form>
                    @endcan
                </div>
            @endforeach
        @else
            <p>Комментариев пока нет.</p>
        @endif

        <!-- Форма добавления комментария -->
        <form action="{{ route('comments.store', $article->id) }}" method="POST" class="comment-form">
            @csrf
            <textarea name="content" rows="3" placeholder="Добавить комментарий..." required></textarea>
            <button type="submit" class="btn">Отправить</button>
        </form>
    </section>
</article>
@endsection
