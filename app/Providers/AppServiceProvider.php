<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('register', function(Request $request) {
            return Limit::perMinutes(5, 20)->by($request->ip());
        });

        RateLimiter::for('login', function(Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('health', function(Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
