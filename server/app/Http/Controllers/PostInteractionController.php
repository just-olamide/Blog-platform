<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Save;
use App\Models\Repost;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PostInteractionController extends Controller
{
    /**
     * Toggle like on a post
     */
    public function toggleLike(Post $post)
    {
        $userId = auth()->id();
        $like = Like::where('user_id', $userId)->where('post_id', $post->id)->first();

        if ($like) {
            $like->delete();
            ActivityLog::log('post_unliked', $post);
            $message = 'Post unliked successfully';
            $liked = false;
        } else {
            Like::create([
                'user_id' => $userId,
                'post_id' => $post->id,
            ]);
            ActivityLog::log('post_liked', $post);
            $message = 'Post liked successfully';
            $liked = true;
        }

        return response()->json([
            'message' => $message,
            'liked' => $liked,
            'likes_count' => $post->likes()->count()
        ]);
    }

    /**
     * Toggle save on a post
     */
    public function toggleSave(Post $post)
    {
        $userId = auth()->id();
        $save = Save::where('user_id', $userId)->where('post_id', $post->id)->first();

        if ($save) {
            $save->delete();
            ActivityLog::log('post_unsaved', $post);
            $message = 'Post removed from saved';
            $saved = false;
        } else {
            Save::create([
                'user_id' => $userId,
                'post_id' => $post->id,
            ]);
            ActivityLog::log('post_saved', $post);
            $message = 'Post saved successfully';
            $saved = true;
        }

        return response()->json([
            'message' => $message,
            'saved' => $saved,
            'saves_count' => $post->saves()->count()
        ]);
    }

    /**
     * Toggle repost on a post
     */
    public function toggleRepost(Post $post)
    {
        $userId = auth()->id();
        $repost = Repost::where('user_id', $userId)->where('post_id', $post->id)->first();

        if ($repost) {
            $repost->delete();
            ActivityLog::log('post_unreposted', $post);
            $message = 'Repost removed successfully';
            $reposted = false;
        } else {
            Repost::create([
                'user_id' => $userId,
                'post_id' => $post->id,
            ]);
            ActivityLog::log('post_reposted', $post);
            $message = 'Post reposted successfully';
            $reposted = true;
        }

        return response()->json([
            'message' => $message,
            'reposted' => $reposted,
            'reposts_count' => $post->reposts()->count()
        ]);
    }

    /**
     * Get user's saved posts
     */
    public function savedPosts()
    {
        $savedPosts = auth()->user()
                           ->savedPosts()
                           ->with(['user', 'comments.user', 'likes', 'saves', 'reposts'])
                           ->latest('saves.created_at')
                           ->paginate(10);

        return response()->json($savedPosts);
    }

    /**
     * Get user's liked posts
     */
    public function likedPosts()
    {
        $likedPosts = auth()->user()
                           ->likedPosts()
                           ->with(['user', 'comments.user', 'likes', 'saves', 'reposts'])
                           ->latest('likes.created_at')
                           ->paginate(10);

        return response()->json($likedPosts);
    }

    /**
     * Get user's reposted posts
     */
    public function repostedPosts()
    {
        $repostedPosts = auth()->user()
                              ->repostedPosts()
                              ->with(['user', 'comments.user', 'likes', 'saves', 'reposts'])
                              ->latest('reposts.created_at')
                              ->paginate(10);

        return response()->json($repostedPosts);
    }
}
