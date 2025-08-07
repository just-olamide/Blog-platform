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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            
            $stats = [
                'totalPosts' => Post::count(),
                'totalUsers' => User::count(),
                'totalComments' => Comment::count(),
                'totalLikes' => Like::count(),
                'newPostsThisMonth' => Post::where('created_at', '>=', $currentMonth)->count(),
                'newUsersThisMonth' => User::where('created_at', '>=', $currentMonth)->count(),
                'newCommentsThisMonth' => Comment::where('created_at', '>=', $currentMonth)->count(),
                'newLikesThisMonth' => Like::where('created_at', '>=', $currentMonth)->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent posts for dashboard
     */
    public function getRecentPosts(): JsonResponse
    {
        try {
            $posts = Post::with('user')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch recent posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(): JsonResponse
    {
        try {
            // Posts over time (last 30 days)
            $postsOverTime = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $count = Post::whereDate('created_at', $date)->count();
                $postsOverTime[] = [
                    'date' => Carbon::now()->subDays($i)->format('M j'),
                    'count' => $count
                ];
            }

            // User activity data
            $totalSaves = Save::count();
            $totalReposts = Repost::count();

            $chartData = [
                'postsOverTime' => $postsOverTime,
                'totalSaves' => $totalSaves,
                'totalReposts' => $totalReposts
            ];

            return response()->json($chartData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export dashboard data as CSV
     */
    public function exportData(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            $filename = 'dashboard-data-' . date('Y-m-d') . '.csv';

            return response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($handle, ['Type', 'ID', 'Title/Name', 'Author/User', 'Created At', 'Status']);

                // Export posts
                Post::with('user')->chunk(100, function ($posts) use ($handle) {
                    foreach ($posts as $post) {
                        fputcsv($handle, [
                            'Post',
                            $post->id,
                            $post->title,
                            $post->user->name,
                            $post->created_at->format('Y-m-d H:i:s'),
                            $post->status
                        ]);
                    }
                });

                // Export users
                User::chunk(100, function ($users) use ($handle) {
                    foreach ($users as $user) {
                        fputcsv($handle, [
                            'User',
                            $user->id,
                            $user->name,
                            $user->email,
                            $user->created_at->format('Y-m-d H:i:s'),
                            $user->role
                        ]);
                    }
                });

                // Export comments
                Comment::with(['user', 'post'])->chunk(100, function ($comments) use ($handle) {
                    foreach ($comments as $comment) {
                        fputcsv($handle, [
                            'Comment',
                            $comment->id,
                            substr($comment->content, 0, 50) . '...',
                            $comment->user->name,
                            $comment->created_at->format('Y-m-d H:i:s'),
                            'Published'
                        ]);
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete post (admin moderation)
     */
    public function deletePost(Request $request, $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            
            // Log the action
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_delete_post',
                'description' => "Admin deleted post: {$post->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $post->delete();

            return response()->json([
                'message' => 'Post deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all posts for admin management
     */
    public function getAllPosts(Request $request): JsonResponse
    {
        try {
            $query = Post::with(['user', 'comments', 'likes'])
                ->withCount(['comments', 'likes']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $posts = $query->paginate($request->get('per_page', 15));

            return response()->json($posts);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users for admin management
     */
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            $query = User::withCount(['posts', 'comments']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role')) {
                $query->where('role', $request->get('role'));
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $users = $query->paginate($request->get('per_page', 15));

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all comments for admin management
     */
    public function getAllComments(Request $request): JsonResponse
    {
        try {
            $query = Comment::with(['user', 'post']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('content', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('post', function ($postQuery) use ($search) {
                          $postQuery->where('title', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $comments = $query->paginate($request->get('per_page', 15));

            return response()->json($comments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete comment (admin moderation)
     */
    public function deleteComment(Request $request, $id): JsonResponse
    {
        try {
            $comment = Comment::findOrFail($id);
            
            // Log the action
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_delete_comment',
                'description' => "Admin deleted comment by {$comment->user->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $comment->delete();

            return response()->json([
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs
     */
    public function getActivityLogs(Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::with('user');

            // Filter by action
            if ($request->has('action')) {
                $query->where('action', $request->get('action'));
            }

            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            // Date range filter
            if ($request->has('from_date')) {
                $query->whereDate('created_at', '>=', $request->get('from_date'));
            }
            if ($request->has('to_date')) {
                $query->whereDate('created_at', '<=', $request->get('to_date'));
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $logs = $query->paginate($request->get('per_page', 20));

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
