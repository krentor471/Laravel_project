<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('layout');
});

Route::get('/about', function(){
    return view('main.about');
});

Route::get('/contact', function(){
    $array = [
        'name' => 'Moscow Polytech',
        'addres' => 'B.Semenovsakay h.38',
        'phone' => '89751259452',
        'email' => '..@mospolytech.ru'
    ];
    return view('main.contact', ['contact'=>$array]);
});