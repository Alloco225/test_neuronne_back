<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::delete('logout', [AuthController::class, 'logout']);
});

Route::get('posts/{slug}', [PostController::class, 'getPostBySlug']);
Route::resource('posts', PostController::class);
