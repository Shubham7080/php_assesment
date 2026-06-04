<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookVersionController;
use App\Http\Controllers\Api\BookWorkflowController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentUploadController;
use App\Http\Controllers\Api\PageController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::apiResource('books', BookController::class);

    Route::get('books/{book}/versions', [BookVersionController::class, 'index']);
    Route::post('books/{book}/versions', [BookVersionController::class, 'store']);
    Route::get('books/{book}/versions/{version}', [BookVersionController::class, 'show']);

    Route::get('books/{book}/chapters', [ChapterController::class, 'index']);
    Route::post('books/{book}/chapters', [ChapterController::class, 'store']);
    Route::put('chapters/{chapter}', [ChapterController::class, 'update']);
    Route::delete('chapters/{chapter}', [ChapterController::class, 'destroy']);

    Route::get('chapters/{chapter}/pages', [PageController::class, 'index']);
    Route::post('chapters/{chapter}/pages', [PageController::class, 'store']);
    Route::put('pages/{page}', [PageController::class, 'update']);
    Route::delete('pages/{page}', [PageController::class, 'destroy']);

    Route::post('books/{book}/upload', [DocumentUploadController::class, 'store']);

    Route::post('books/{book}/submit', [BookWorkflowController::class, 'submit']);
    Route::post('books/{book}/approve', [BookWorkflowController::class, 'approve']);
    Route::post('books/{book}/reject', [BookWorkflowController::class, 'reject']);
    Route::post('books/{book}/publish', [BookWorkflowController::class, 'publish']);
});
