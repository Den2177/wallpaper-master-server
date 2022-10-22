<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiToken;
use App\Http\Middleware\DemoToken;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(DemoToken::class)->group(function() {
    Route::post('/auth', [AuthController::class, 'auth']);

    Route::get('/top', [ImageController::class, 'getTopImages']);
    Route::get('/recommended', [ImageController::class, 'getRecommendedImages']);

    Route::prefix('/images')->group(function() {
        Route::get('/{image}', [ImageController::class, 'show']);
    });

    Route::prefix('/users')->group(function() {
        Route::get('/statistic', [UserController::class, 'getStatistic']);
        Route::get('/{user}', [UserController::class, 'index']);
        Route::get('/{user}/images', [ImageController::class, 'getUserImages']);
        Route::patch('/profile', [UserController::class, 'updateProfile']);
    });
});

Route::middleware(ApiToken::class)->group(function() {
    Route::prefix('/images')->group(function() {
        Route::get('/', [ImageController::class, 'index']);
        Route::post('/', [ImageController::class, 'store']);
        Route::get('/{image}/like', [ImageController::class, 'toggleLike']);
        Route::get('/{image}/download', [ImageController::class, 'download']);
    });

    Route::get('/statistic/{user}', [StatisticController::class, 'index']);
    Route::get('/my-statistic', [StatisticController::class, 'getMyStatistic']);
    Route::get('/liked', [ImageController::class, 'indexLiked']);
});



