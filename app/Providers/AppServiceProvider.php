<?php

namespace App\Providers;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\ArticleServiceInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\UserPreferenceRepositoryInterface;
use App\Contracts\UserPreferenceServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Helpers\RateLimitHelper;
use App\Repositories\ArticleRepository;
use App\Repositories\UserPreferenceRepository;
use App\Repositories\UserRepository;
use App\Services\ArticleService;
use App\Services\AuthService;
use App\Services\UserPreferenceService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(ArticleServiceInterface::class, ArticleService::class);
        $this->app->bind(UserPreferenceServiceInterface::class, UserPreferenceService::class);
        $this->app->bind(UserPreferenceRepositoryInterface::class, UserPreferenceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(5, 20)->by($request->ip())->response(function () use ($request) {
                return RateLimitHelper::rateLimitResponse($request);
            });
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () use ($request) {
                return RateLimitHelper::rateLimitResponse($request);
            });
        });

        RateLimiter::for('health', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () use ($request) {
                return RateLimitHelper::rateLimitResponse($request);
            });
        });
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () use ($request) {
                return RateLimitHelper::rateLimitResponse($request);
            });
        });
        RateLimiter::for('articles', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () use ($request) {
                return RateLimitHelper::rateLimitResponse($request);
            });
        });

        RateLimiter::for('user-preferences', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () use ($request) {
                return RateLimitHelper::rateLimitResponse($request);
            });
        });
    }
}
