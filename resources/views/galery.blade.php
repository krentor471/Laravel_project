@extends('layouts.app')

@section('title', 'Галерея')

@section('content')
@if($article)
<section>
    <h1>{{ $article['name'] }}</h1>
    <p><strong>Дата:</strong> {{ $article['date'] }}</p>

    <div style="margin: 20px 0; text-align: center;">
        <img src="{{ asset($article['full_image']) }}" alt="full image" style="max-width: 100%; height: auto;">
    </div>

    <p>{{ $article['desc'] }}</p>

    <a href="{{ route('home') }}" style="display: inline-block; margin-top: 20px; color: var(--accent-color);">← Назад к списку</a>
</section>
@else
<section>
    <h1>Статья не найдена</h1>
    <a href="{{ route('home') }}">← Назад к списку</a>
</section>
@endif
@endsection
