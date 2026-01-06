@extends('layouts.app')

@section('title', 'Редактировать статью')

@section('content')
<h1>Редактировать статью</h1>

<form action="{{ route('articles.update', $article->id) }}" method="POST" class="article-form">
    @csrf
    @method('PUT')

    <label for="title">Заголовок:</label>
    <input type="text" id="title" name="title" value="{{ old('title', $article->title) }}" required>
    @error('title')
        <span class="error">{{ $message }}</span>
    @enderror

    <label for="author">Автор:</label>
    <input type="text" id="author" name="author" value="{{ old('author', $article->author) }}">
    @error('author')
        <span class="error">{{ $message }}</span>
    @enderror

    <label for="content">Содержание:</label>
    <textarea id="content" name="content" rows="10" required>{{ old('content', $article->content) }}</textarea>
    @error('content')
        <span class="error">{{ $message }}</span>
    @enderror

    <div class="form-actions">
        <button type="submit">Сохранить</button>
        <a href="{{ route('articles.index') }}" class="btn-secondary">Отмена</a>
    </div>
</form>
@endsection
