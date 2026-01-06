<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Новая статья</title>
</head>
<body>
    <h1>Добавлена новая статья</h1>

    <h2>{{ $article->title }}</h2>

    <p><strong>Автор:</strong> {{ $article->author ?? 'Неизвестен' }}</p>

    <p><strong>Дата публикации:</strong> {{ $article->created_at->format('d.m.Y H:i') }}</p>

    <h3>Содержание:</h3>
    <p>{{ $article->content }}</p>
</body>
</html>
