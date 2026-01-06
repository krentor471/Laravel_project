<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('home') }}">Главная</a>
            <a href="{{ route('about') }}">О нас</a>
            <a href="{{ route('contacts') }}">Контакты</a>
            <a href="{{ route('articles.index') }}">Новости</a>
            @auth
                @can('create', \App\Models\Article::class)
                    <a href="{{ route('articles.create') }}">Создать новость</a>
                @endcan
                @if(Auth::user()->isModerator())
                    <a href="{{ route('comments.moderation') }}">Модерация</a>
                @endif
            @endauth
        </nav>

        <div class="auth-section">
            @auth
                <div class="notifications-dropdown">
                    <button class="notifications-btn">
                        Уведомления
                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="badge">{{ Auth::user()->unreadNotifications->count() }}</span>
                        @endif
                    </button>
                    <div class="notifications-list">
                        @forelse(Auth::user()->unreadNotifications as $notification)
                            <a href="{{ route('notification.read', $notification->id) }}" class="notification-item">
                                {{ $notification->data['message'] ?? 'Новое уведомление' }}
                            </a>
                        @empty
                            <div class="notification-item">Нет новых уведомлений</div>
                        @endforelse
                    </div>
                </div>
                <span class="user-name">{{ Auth::user()->name }}</span>
                <a href="{{ route('logout') }}" class="btn-secondary">Выход</a>
            @else
                <a href="{{ route('login.form') }}" class="btn">Вход</a>
                <a href="{{ route('signin') }}" class="btn-secondary">Регистрация</a>
            @endauth
        </div>
    </header>

    <div id="app">
        <!-- Main -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <p>Колобанов Алексей Юрьевич 241-321</p>
    </footer>

    <script src="{{ asset('auth.js') }}"></script>
</body>
</html>
