@extends('layouts.app')

@section('title', 'Создать статью')

@section('content')
<h1>Создать статью</h1>

<form action="{{ route('articles.store') }}" method="POST" class="article-form">
    @csrf

    <label for="title">Заголовок:</label>
    <input type="text" id="title" name="title" value="{{ old('title') }}" required>
    @error('title')
        <span class="error">{{ $message }}</span>
    @enderror

    <label for="author">Автор:</label>
    <input type="text" id="author" name="author" value="{{ old('author') }}">
    @error('author')
        <span class="error">{{ $message }}</span>
    @enderror

    <label for="content">Содержание:</label>
    <textarea id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
    @error('content')
        <span class="error">{{ $message }}</span>
    @enderror

    <div class="form-actions">
        <button type="submit">Создать</button>
        <a href="{{ route('articles.index') }}" class="btn-secondary">Отмена</a>
    </div>
</form>
@endsection
