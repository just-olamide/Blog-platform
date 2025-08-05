<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the likes for the post.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the saves for the post.
     */
    public function saves()
    {
        return $this->hasMany(Save::class);
    }

    /**
     * Get the reposts for the post.
     */
    public function reposts()
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Get the users who liked this post.
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    /**
     * Get the users who saved this post.
     */
    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saves');
    }

    /**
     * Get the users who reposted this post.
     */
    public function repostedByUsers()
    {
        return $this->belongsToMany(User::class, 'reposts');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the total number of likes for this post.
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Get the total number of comments for this post.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Get the total number of reposts for this post.
     */
    public function getRepostsCountAttribute()
    {
        return $this->reposts()->count();
    }
}
