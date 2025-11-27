<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// Public route: user login.
// POST /api/login -> returns a personal access token on success.
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Simple ping for debug
Route::get('/_ping', function () {
    return response()->json(['pong' => true]);
});

// Task routes protected by Sanctum token auth.
// All routes inside this group require a valid Bearer token created by /api/login.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);

    // Status transition routes as requested (use POST)
    Route::post('/tasks/{id}/in-process', [TaskController::class, 'inProcess']);
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete']);

    // Show a single task owned by the authenticated user
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
});

// If you want these routes protected by Sanctum, wrap them like:
// Route::middleware('auth:sanctum')->group(function () { ... });
