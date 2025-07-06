<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login'])->middleware('guest');
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::prefix('tasks')->group(function () {
        Route::apiResource('', TaskController::class)->except(['store']);
        Route::post('', [TaskController::class, 'store'])->middleware('throttle:task-creation');
        Route::patch('{task}/update-status', [TaskController::class, 'updateStatus']);
    });
});
