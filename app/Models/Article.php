<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'author'];

    // Отношение к комментариям
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
