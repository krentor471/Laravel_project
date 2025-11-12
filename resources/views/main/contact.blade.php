@extends('layout')
@section('content')
    <div class="card" style="width: 18rem;">
  <div class="card-body">
    <h5 class="card-title">{{ $contact['name']}}</h5>
    <h6 class="card-subtitle mb-2 text-body-secondary">{{ $contact['addres'] }}</h6>
    <h6 class="card-subtitle mb-2 text-body-secondary">{{ $contact['phone']}}</h6>
    <a href="#" class="card-link">{{$contact['email']}}</a>
  </div>
</div>
@endsection