<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Save;
use App\Models\Repost;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                return response()->json(['message' => 'Admin access required'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get dashboard overview statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'total_comments' => Comment::count(),
            'total_likes' => Like::count(),
            'total_saves' => Save::count(),
            'total_reposts' => Repost::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'regular_users' => User::where('role', 'user')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get posts analytics over time (for line chart)
     */
    public function postsAnalytics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);

        $postsData = Post::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $likesData = Like::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $commentsData = Comment::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'posts' => $postsData,
            'likes' => $likesData,
            'comments' => $commentsData,
        ]);
    }

    /**
     * Get user statistics (for pie chart)
     */
    public function userStatistics()
    {
        $userStats = [
            'admin' => User::where('role', 'admin')->count(),
            'user' => User::where('role', 'user')->count(),
        ];

        $postStats = [
            'published' => Post::where('status', 'published')->count(),
            'draft' => Post::where('status', 'draft')->count(),
        ];

        return response()->json([
            'user_roles' => $userStats,
            'post_status' => $postStats,
        ]);
    }

    /**
     * Get all posts for moderation
     */
    public function posts(Request $request)
    {
        $query = Post::with(['user', 'comments', 'likes', 'saves', 'reposts']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $posts = $query->latest()->paginate(15);

        return response()->json($posts);
    }

    /**
     * Get all comments for moderation
     */
    public function comments(Request $request)
    {
        $query = Comment::with(['user', 'post']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $comments = $query->latest()->paginate(20);

        return response()->json($comments);
    }

    /**
     * Get all users for management
     */
    public function users(Request $request)
    {
        $query = User::withCount(['posts', 'comments', 'likes']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return response()->json($users);
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,user'
        ]);

        $user->update(['role' => $request->role]);

        ActivityLog::log('user_role_updated', $user, [
            'new_role' => $request->role
        ]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get activity logs
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(25);

        return response()->json($logs);
    }

    /**
     * Delete user (admin only)
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting the last admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'message' => 'Cannot delete the last admin user'
            ], 422);
        }

        ActivityLog::log('user_deleted', $user);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Force delete post (admin only)
     */
    public function forceDeletePost(Post $post)
    {
        ActivityLog::log('post_force_deleted', $post);
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * Force delete comment (admin only)
     */
    public function forceDeleteComment(Comment $comment)
    {
        ActivityLog::log('comment_force_deleted', $comment);
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
