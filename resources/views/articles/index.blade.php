@extends('layouts.app')

@section('title', 'Новости')

@section('content')
<div class="articles-header">
    <h1>Новости</h1>
    @can('create', \App\Models\Article::class)
        <a href="{{ route('articles.create') }}" class="btn">Создать статью</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<div class="articles-list">
    @forelse($articles as $article)
        <article class="article-item">
            <h2><a href="{{ route('articles.show', $article->id) }}">{{ $article->title }}</a></h2>
            <div class="article-meta">
                <span>Автор: {{ $article->author ?? 'Неизвестен' }}</span>
                <span>{{ $article->created_at->format('d.m.Y H:i') }}</span>
            </div>
            <p>{{ Str::limit($article->content, 200) }}</p>
            <div class="article-actions">
                <a href="{{ route('articles.show', $article->id) }}" class="btn-link">Читать далее</a>
                @can('update', $article)
                    <a href="{{ route('articles.edit', $article->id) }}" class="btn-secondary">Редактировать</a>
                @endcan
                @can('delete', $article)
                    <form action="{{ route('articles.destroy', $article->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete" onclick="return confirm('Удалить статью?')">Удалить</button>
                    </form>
                @endcan
            </div>
        </article>
    @empty
        <p>Нет статей</p>
    @endforelse
</div>

<div class="pagination">
    {{ $articles->links('vendor.pagination.default') }}
</div>
@endsection
