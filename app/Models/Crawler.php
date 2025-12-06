<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Crawler extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'url',
        'list_title_selector',
        'post_title_selector',
        'post_content_selector',
        'board_id',
        'topic_id',
        'author_nickname',
        'use_random_user',
        'is_active',
        'bypass_cloudflare',
        'total_count',
        'last_crawled_at',
    ];

    protected $casts = [
        'use_random_user' => 'boolean',
        'is_active' => 'boolean',
        'bypass_cloudflare' => 'boolean',
        'total_count' => 'integer',
        'last_crawled_at' => 'datetime',
    ];

    /**
     * Get the site that owns the crawler.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the board that the crawler targets.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the topic that the crawler uses.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}


