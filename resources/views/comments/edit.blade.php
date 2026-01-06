@extends('layouts.app')

@section('title', 'Редактировать комментарий')

@section('content')
<div class="container">
    <h1>Редактировать комментарий</h1>

    <form action="{{ route('comments.update', $comment->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="content">Комментарий</label>
            <textarea name="content" id="content" rows="4" required>{{ old('content', $comment->content) }}</textarea>
            @error('content')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn">Сохранить</button>
        <a href="{{ route('articles.show', $comment->article_id) }}" class="btn-secondary">Отмена</a>
    </form>
</div>
@endsection
