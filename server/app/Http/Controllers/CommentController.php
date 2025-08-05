<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a newly created comment
     */
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'content' => $request->content,
        ]);

        ActivityLog::log('comment_created', $comment);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user')
        ], 201);
    }

    /**
     * Update the specified comment
     */
    public function update(Request $request, Comment $comment)
    {
        // Check if user owns the comment or is admin
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        ActivityLog::log('comment_updated', $comment);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment->load('user')
        ]);
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Comment $comment)
    {
        // Check if user owns the comment or is admin
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        ActivityLog::log('comment_deleted', $comment);
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Get comments for a specific post
     */
    public function index(Post $post)
    {
        $comments = $post->comments()
                         ->with('user')
                         ->latest()
                         ->paginate(20);

        return response()->json($comments);
    }
}
