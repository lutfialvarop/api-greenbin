<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::prefix('article')->group(function () {
        Route::get('/get-top-5', [ArticleController::class, 'getTop5']);
        Route::post('/create', [ArticleController::class, 'create']);
        Route::get('/get-all', [ArticleController::class, 'getAll']);
        Route::get('/detail/{id}', [ArticleController::class, 'getDetail']);
    });

    Route::prefix('transaction')->group(function () {
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/create', [TransactionController::class, 'create']);
            Route::get('/history', [TransactionController::class, 'history']);
        });
        Route::put('/update/{id}', [TransactionController::class, 'update']);
    });

    Route::prefix('reward')->group(function () {
        Route::get('/get-all', [RewardController::class, 'getAll']);
        Route::post('/create', [RewardController::class, 'create']);
        Route::get('/detail/{id}', [RewardController::class, 'detail'])->middleware('auth:sanctum');
    });

    Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [UserController::class, 'getDetailProfile']);
        Route::get('/point', [UserController::class, 'getPoint']);
        Route::get('/badge', [UserController::class, 'getBadge']);
    });

    Route::prefix('leaderboard')->group(function () {
        Route::post('/', [UserController::class, 'leaderboard']);
    });
});
