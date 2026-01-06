@extends('layouts.app')

@section('title', 'Вход')

@section('content')
<section>
    <h1>Вход</h1>

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Войти</button>
    </form>

    @if($errors->any())
    <div class="result error">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</section>
@endsection
