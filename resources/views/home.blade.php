@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<h1>Статьи</h1>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border: 1px solid var(--border-color); padding: 10px;">Дата</th>
            <th style="border: 1px solid var(--border-color); padding: 10px;">Название</th>
            <th style="border: 1px solid var(--border-color); padding: 10px;">Превью</th>
            <th style="border: 1px solid var(--border-color); padding: 10px;">Описание</th>
        </tr>
    </thead>
    <tbody>
        @foreach($articles as $index => $article)
        <tr>
            <td style="border: 1px solid var(--border-color); padding: 10px;">{{ $article['date'] }}</td>
            <td style="border: 1px solid var(--border-color); padding: 10px;">{{ $article['name'] }}</td>
            <td style="border: 1px solid var(--border-color); padding: 10px; text-align: center;">
                <a href="{{ route('galery', $index) }}">
                    <img src="{{ asset($article['preview_image']) }}" alt="preview" style="max-width: 100px; height: auto;">
                </a>
            </td>
            <td style="border: 1px solid var(--border-color); padding: 10px;">
                {{ $article['shortDesc'] ?? substr($article['desc'], 0, 100) . '...' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
