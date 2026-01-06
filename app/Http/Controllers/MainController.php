<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
    {
        // Читаем JSON файл
        $jsonPath = public_path('articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        return view('home', ['articles' => $articles]);
    }

    public function galery($id)
    {
        // Читаем JSON файл
        $jsonPath = public_path('articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        $article = $articles[$id] ?? null;

        return view('galery', ['article' => $article]);
    }
}
