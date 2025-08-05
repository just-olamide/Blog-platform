<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'comments.user', 'likes', 'saves', 'reposts'])
                    ->published()
                    ->latest();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $posts = $query->paginate(10);

        return response()->json($posts);
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'in:draft,published'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'status' => $request->status ?? 'draft',
        ]);

        ActivityLog::log('post_created', $post);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load(['user', 'comments.user', 'likes', 'saves', 'reposts'])
        ], 201);
    }

    /**
     * Display the specified post
     */
    public function show(Post $post)
    {
        $post->load(['user', 'comments.user', 'likes', 'saves', 'reposts']);
        
        return response()->json($post);
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, Post $post)
    {
        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|in:draft,published'
        ]);

        $data = $request->only(['title', 'content', 'status']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        ActivityLog::log('post_updated', $post);

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->load(['user', 'comments.user', 'likes', 'saves', 'reposts'])
        ]);
    }

    /**
     * Remove the specified post
     */
    public function destroy(Post $post)
    {
        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete image if exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        ActivityLog::log('post_deleted', $post);
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * Get user's own posts (including drafts)
     */
    public function myPosts(Request $request)
    {
        $posts = Post::with(['user', 'comments.user', 'likes', 'saves', 'reposts'])
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->paginate(10);

        return response()->json($posts);
    }
}
