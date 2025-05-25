<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\TaskAssignmentController;
use App\Http\Controllers\API\EmployeeController;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register');
    Route::post('logout', 'logout')->middleware('auth:api');
    Route::get('me', 'me')->middleware('auth:api');
    Route::get('refresh', 'refresh')->middleware('auth:api');
});

Route::apiResource('tasks', TaskController::class)->middleware('auth:api');
Route::apiResource('task-assignments', TaskAssignmentController::class)->only(['store', 'update'])->middleware('auth:api');
Route::apiResource('employees', EmployeeController::class)->middleware('auth:api');


// Route::fallback(function () {
//     return response()->json([
//         'success' => false,
//         'message' => 'Page not found!',
//     ], 404);
// });