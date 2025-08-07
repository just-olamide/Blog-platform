<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public blog routes (no authentication required)
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Post management routes
    Route::get('/my-posts', [PostController::class, 'myPosts']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    
    // Comment routes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Post interaction routes
    Route::post('/posts/{post}/like', [PostInteractionController::class, 'toggleLike']);
    Route::post('/posts/{post}/save', [PostInteractionController::class, 'toggleSave']);
    Route::post('/posts/{post}/repost', [PostInteractionController::class, 'toggleRepost']);
    
    // User's interaction history
    Route::get('/saved-posts', [PostInteractionController::class, 'savedPosts']);
    Route::get('/liked-posts', [PostInteractionController::class, 'likedPosts']);
    Route::get('/reposted-posts', [PostInteractionController::class, 'repostedPosts']);
    
    // Admin routes (require admin role)
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        
        // Dashboard data
        Route::get('/stats', [AdminController::class, 'getStats']);
        Route::get('/recent-posts', [AdminController::class, 'getRecentPosts']);
        Route::get('/chart-data', [AdminController::class, 'getChartData']);
        Route::get('/export', [AdminController::class, 'exportData']);
        
        // Content management
        Route::get('/posts', [AdminController::class, 'getAllPosts']);
        Route::delete('/posts/{id}', [AdminController::class, 'deletePost']);
        
        Route::get('/users', [AdminController::class, 'getAllUsers']);
        
        Route::get('/comments', [AdminController::class, 'getAllComments']);
        Route::delete('/comments/{id}', [AdminController::class, 'deleteComment']);
        
        Route::get('/logs', [AdminController::class, 'getActivityLogs']);
        
    });
    
});
