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
    Route::prefix('admin')->group(function () {
        
        // Dashboard and analytics
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/analytics/posts', [AdminController::class, 'postsAnalytics']);
        Route::get('/analytics/users', [AdminController::class, 'userStatistics']);
        
        // Content moderation
        Route::get('/posts', [AdminController::class, 'posts']);
        Route::get('/comments', [AdminController::class, 'comments']);
        Route::delete('/posts/{post}/force', [AdminController::class, 'forceDeletePost']);
        Route::delete('/comments/{comment}/force', [AdminController::class, 'forceDeleteComment']);
        
        // User management
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        
        // Activity logs
        Route::get('/activity-logs', [AdminController::class, 'activityLogs']);
        
    });
    
});
