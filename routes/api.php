<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    TaskController,
    UserController
};



Route::post('login', [AuthController::class, 'login'])->middleware('guest');

Route::group(['middleware' => ['auth:sanctum', 'throttle:task-creation']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users', UserController::class)->middleware('ability:user:index');

    Route::apiResource('tasks', TaskController::class)->except(['store', 'update', 'destroy']);
    Route::prefix('tasks')->group(function () {

        Route::post('', [TaskController::class, 'store'])
            ->middleware(['ability:task:create']);

        Route::put('{task}/update', [TaskController::class, 'update'])
            ->middleware(['ability:task:update']);

        Route::patch('{task}/assign-user', [TaskController::class, 'assignUserToTask'])
            ->middleware('ability:user:index');

        Route::patch('{task}/update-status', [TaskController::class, 'updateStatus'])
            ->middleware('ability:task:update-status');

        Route::post('{task}/add-dependencies', [TaskController::class, 'addDependency'])
            ->middleware('ability:task:add-dependencies');
    });
});
