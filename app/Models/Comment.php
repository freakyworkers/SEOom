<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'is_adopted',
        'adoption_points',
    ];

    protected $casts = [
        'is_adopted' => 'boolean',
        'adoption_points' => 'integer',
    ];

    /**
     * Get the site that owns the comment.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Check if comment has replies.
     */
    public function hasReplies()
    {
        return $this->replies()->count() > 0;
    }

    /**
     * Scope a query to only include top-level comments.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update storage when comment is created, updated, or deleted
        static::saved(function ($comment) {
            if ($comment->site_id && $comment->site) {
                try {
                    dispatch(function() use ($comment) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($comment->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for comment: ' . $e->getMessage());
                }
            }
        });

        static::deleted(function ($comment) {
            if ($comment->site_id && $comment->site) {
                try {
                    dispatch(function() use ($comment) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($comment->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for deleted comment: ' . $e->getMessage());
                }
            }
        });
    }
}

