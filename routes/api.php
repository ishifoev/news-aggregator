<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::middleware(['throttle:register'])->post('/register', [AuthController::class, 'register']);
    Route::middleware(['throttle:login'])->post('/login', [AuthController::class, 'login']);
    Route::middleware(['verify.health.token', 'throttle:health'])->get('/health', [HealthCheckController::class, 'status']);

    Route::post('/password-reset', [AuthController::class, 'passwordReset']);

    Route::middleware(['throttle:articles'])->group(function () {
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/articles/{id}', [ArticleController::class, 'show']);
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
