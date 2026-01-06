@extends('layouts.app')

@section('title', 'Контакты')

@section('content')
<section>
    <h1>Контакты</h1>
    @foreach($contacts as $contact)
    <div class="contact-item">
        <p><strong>ФИО:</strong> {{ $contact['person'] }}</p>
        <p><strong>Телефон:</strong> {{ $contact['phone'] }}</p>
        <p><strong>Email:</strong> {{ $contact['email'] }}</p>
    </div>
    @endforeach
</section>
@endsection
