<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

        Route::post('/tasks/{taskId}/comments', [CommentController::class, 'store']);
        Route::get('/tasks/{taskId}/comments', [CommentController::class, 'index']);
        Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

        Route::get('/teams', [TeamController::class, 'index']);
        Route::post('/teams', [TeamController::class, 'store']);
        Route::get('/teams/{team}', [TeamController::class, 'show']);
        Route::put('/teams/{team}', [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
        Route::post('/teams/{team}/users', [TeamController::class, 'addUser']);
        Route::delete('/teams/{team}/users/{user}', [TeamController::class, 'removeUser']);
    });
});
