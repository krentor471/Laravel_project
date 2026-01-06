<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Новая статья</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .article-title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .meta {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .article-content {
            margin-top: 15px;
            padding: 15px;
            background-color: white;
            border-left: 4px solid #4CAF50;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Новая статья опубликована!</h1>
    </div>

    <div class="content">
        <h2 class="article-title">{{ $article->title }}</h2>

        <div class="meta">
            <strong>Автор:</strong> {{ $article->author ?? 'Неизвестен' }}<br>
            <strong>Дата публикации:</strong> {{ $article->created_at->format('d.m.Y H:i') }}
        </div>

        <div class="article-content">
            <h3>Содержание:</h3>
            <p>{{ Str::limit($article->content, 200) }}</p>
        </div>

        <p style="margin-top: 20px;">
            <a href="{{ url('/articles/' . $article->id) }}"
               style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Читать полностью
            </a>
        </p>
    </div>

    <div class="footer">
        <p>Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.</p>
    </div>
</body>
</html>
