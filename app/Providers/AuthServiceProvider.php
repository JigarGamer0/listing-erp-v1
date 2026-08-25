<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // Implicitly grant "Main Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Main Admin') ? true : null;
        });
    }
}
