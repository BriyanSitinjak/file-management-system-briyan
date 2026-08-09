<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\TrashController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('folders', FolderController::class);
    Route::apiResource('files', FileController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::get('/files/{file}/preview', [FileController::class, 'preview']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/trash', [TrashController::class, 'index']);
    Route::post('/trash/folders/{folder}/restore', [TrashController::class, 'restoreFolder']);
    Route::post('/trash/files/{file}/restore', [TrashController::class, 'restoreFile']);
});
