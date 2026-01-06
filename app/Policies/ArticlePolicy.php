<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user)
    {
        return true;
    }

    public function view(?User $user, Article $article)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isModerator()
            ? $this->allow()
            : $this->deny('Только модераторы могут создавать статьи');
    }

    public function update(User $user, Article $article)
    {
        return $user->isModerator()
            ? $this->allow()
            : $this->deny('Только модераторы могут редактировать статьи');
    }

    public function delete(User $user, Article $article)
    {
        return $user->isModerator()
            ? $this->allow()
            : $this->deny('Только модераторы могут удалять статьи');
    }

    public function restore(User $user, Article $article)
    {
        return $user->isModerator();
    }

    public function forceDelete(User $user, Article $article)
    {
        return $user->isModerator();
    }
}
