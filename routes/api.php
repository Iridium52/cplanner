<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Token info
    Route::get('/me', [TokenController::class, 'me']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    // Tasks
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::patch('/tasks/{task}/resolve', [TaskController::class, 'resolve']);
    Route::patch('/tasks/{task}/reopen', [TaskController::class, 'reopen']);
});
