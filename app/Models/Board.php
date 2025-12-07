<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'type',
        'event_display_type',
        'summary_length',
        'description',
        'header_image_path',
        'post_template',
        'footer_content',
        'order',
        'is_active',
        'random_order',
        'allow_multiple_topics',
        'remove_links',
        'enable_likes',
        'saved_posts_enabled',
        'max_posts_per_day',
        'posts_per_page',
        'seo_title',
        'seo_description',
        'read_permission',
        'write_permission',
        'delete_permission',
        'comment_permission',
        'comment_delete_permission',
        'read_points',
        'write_points',
        'delete_points',
        'comment_points',
        'comment_delete_points',
        'enable_anonymous',
        'enable_secret',
        'force_secret',
        'enable_reply',
        'enable_comments',
        'exclude_from_rss',
        'prevent_drag',
        'enable_attachments',
        'enable_author_comment_adopt',
        'enable_admin_comment_adopt',
        'banned_words',
        'pinterest_columns_mobile',
        'pinterest_columns_tablet',
        'pinterest_columns_desktop',
        'pinterest_columns_large',
        'qa_statuses',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'random_order' => 'boolean',
        'allow_multiple_topics' => 'boolean',
        'remove_links' => 'boolean',
        'enable_likes' => 'boolean',
        'saved_posts_enabled' => 'boolean',
        'enable_anonymous' => 'boolean',
        'enable_secret' => 'boolean',
        'force_secret' => 'boolean',
        'enable_reply' => 'boolean',
        'enable_comments' => 'boolean',
        'exclude_from_rss' => 'boolean',
        'prevent_drag' => 'boolean',
        'enable_attachments' => 'boolean',
        'enable_author_comment_adopt' => 'boolean',
        'enable_admin_comment_adopt' => 'boolean',
        'order' => 'integer',
        'max_posts_per_day' => 'integer',
        'read_points' => 'integer',
        'write_points' => 'integer',
        'delete_points' => 'integer',
        'comment_points' => 'integer',
        'comment_delete_points' => 'integer',
        'summary_length' => 'integer',
        'pinterest_columns_mobile' => 'integer',
        'pinterest_columns_tablet' => 'integer',
        'pinterest_columns_desktop' => 'integer',
        'pinterest_columns_large' => 'integer',
        'qa_statuses' => 'array',
    ];

    /**
     * 게시판 타입 상수
     */
    const TYPE_GENERAL = 'general';
    const TYPE_PHOTO = 'photo';
    const TYPE_BOOKMARK = 'bookmark';
    const TYPE_BLOG = 'blog';
    const TYPE_CLASSIC = 'classic';
    const TYPE_INSTAGRAM = 'instagram';
    const TYPE_EVENT = 'event';
    const TYPE_ONE_ON_ONE = 'one_on_one';
    const TYPE_PINTEREST = 'pinterest';
    const TYPE_QA = 'qa';

    /**
     * 게시판 타입 목록
     */
    public static function getTypes()
    {
        return [
            self::TYPE_GENERAL => '일반 게시판',
            self::TYPE_PHOTO => '사진 게시판',
            self::TYPE_BOOKMARK => '북마크 게시판',
            self::TYPE_BLOG => '블로그',
            self::TYPE_CLASSIC => '클래식 게시판',
            self::TYPE_INSTAGRAM => '인스타그램',
            self::TYPE_EVENT => '이벤트',
            self::TYPE_ONE_ON_ONE => '1:1 게시판',
            self::TYPE_PINTEREST => '핀터레스트',
            self::TYPE_QA => '질의응답',
        ];
    }

    /**
     * Get the site that owns the board.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the posts for the board.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get active posts for the board.
     */
    public function activePosts()
    {
        return $this->posts()->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include active boards.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order boards.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Get the topics for the board.
     */
    public function topics()
    {
        return $this->hasMany(Topic::class)->ordered();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update storage when board is created, updated, or deleted
        static::saved(function ($board) {
            if ($board->site_id && $board->site) {
                try {
                    dispatch(function() use ($board) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($board->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for board: ' . $e->getMessage());
                }
            }
        });

        static::deleted(function ($board) {
            if ($board->site_id && $board->site) {
                try {
                    dispatch(function() use ($board) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($board->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for deleted board: ' . $e->getMessage());
                }
            }
        });
    }
}

