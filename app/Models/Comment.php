<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['article_id', 'user_id', 'content', 'is_approved'];

    // Отношение к статье
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Отношение к пользователю
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
