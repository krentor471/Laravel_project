@extends('layouts.app')

@section('title', 'Модерация комментариев')

@section('content')
<div class="container">
    <h1>Модерация комментариев</h1>

    @if($comments->count() > 0)
        @foreach($comments as $comment)
            <div class="comment-moderation">
                <h3>К статье: <a href="{{ route('articles.show', $comment->article_id) }}">{{ $comment->article->title }}</a></h3>
                <p><strong>Автор:</strong> {{ $comment->user ? $comment->user->name : 'Аноним' }}</p>
                <p><strong>Комментарий:</strong> {{ $comment->content }}</p>
                <p><small>{{ $comment->created_at->format('d.m.Y H:i') }}</small></p>

                <div class="moderation-actions">
                    <form action="{{ route('comments.approve', $comment->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn">Одобрить</button>
                    </form>

                    <form action="{{ route('comments.reject', $comment->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-delete" onclick="return confirm('Отклонить комментарий?')">Отклонить</button>
                    </form>
                </div>
                <hr>
            </div>
        @endforeach
    @else
        <p>Нет комментариев на модерации.</p>
    @endif
</div>
@endsection
