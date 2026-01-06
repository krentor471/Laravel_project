<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новый комментарий</title>
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
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .info-block {
            background-color: white;
            border: 1px solid #eee;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Новый комментарий требует модерации</h1>
    </div>
    
    <div class="content">
        <p>Здравствуйте!</p>
        <p>На сайте <strong>{{ config('app.name') }}</strong> был добавлен новый комментарий, который ожидает вашей проверки.</p>
        
        <div class="info-block">
            <h3>Информация о комментарии:</h3>
            <p><strong>Статья:</strong> {{ $article->title }}</p>
            <p><strong>Автор:</strong> {{ $user->name }} ({{ $user->email }})</p>
            <p><strong>Дата:</strong> {{ $comment->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Текст комментария:</strong></p>
            <blockquote style="background: #f4f4f4; padding: 10px; border-left: 3px solid #4CAF50;">
                {{ $comment->content }}
            </blockquote>
        </div>
        
        <div class="info-block">
            <h3>Действия:</h3>
            <p>Для просмотра комментария и выполнения модерации перейдите по ссылке:</p>
            <a href="{{ url('/comments/moderation') }}" class="btn">
                Перейти к модерации
            </a>
            <p style="font-size: 12px; color: #666; margin-top: 5px;">
                Если кнопка не работает, скопируйте ссылку: {{ url('/comments/moderation') }}
            </p>
        </div>
        
        <p>С уважением,<br>Система уведомлений {{ config('app.name') }}</p>
    </div>
    
    <div class="footer">
        <p>Это письмо было отправлено автоматически. Пожалуйста, не отвечайте на него.</p>
        <p>Если вы получили это письмо по ошибке, пожалуйста, проигнорируйте его.</p>
    </div>
</body>
</html>