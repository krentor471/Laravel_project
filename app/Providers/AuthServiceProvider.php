<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Article' => 'App\Policies\ArticlePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user->isModerator()) {
                return true;
            }
        });

        // Gates для комментариев
        Gate::define('update-comment', function ($user, $comment) {
            return $user->id === $comment->user_id;
        });

        Gate::define('delete-comment', function ($user, $comment) {
            return $user->id === $comment->user_id;
        });
    }
}
