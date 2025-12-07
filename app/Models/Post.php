<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'board_id',
        'user_id',
        'title',
        'content',
        'external_url',
        'thumbnail_path',
        'site_name',
        'code',
        'link',
        'bookmark_items',
        'views',
        'is_notice',
        'is_pinned',
        'is_secret',
        'adoption_points',
        'adopted_comment_id',
        'reply_to',
        'event_start_date',
        'event_end_date',
        'event_end_undecided',
        'event_type',
        'event_is_ended',
        'qa_status',
    ];

    protected $casts = [
        'views' => 'integer',
        'is_notice' => 'boolean',
        'is_pinned' => 'boolean',
        'is_secret' => 'boolean',
        'adoption_points' => 'integer',
        'adopted_comment_id' => 'integer',
        'bookmark_items' => 'array',
        'event_start_date' => 'date',
        'event_end_date' => 'date',
        'event_end_undecided' => 'boolean',
        'event_is_ended' => 'boolean',
    ];

    /**
     * Get the site that owns the post.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the board that owns the post.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

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
     * Get the attachments for the post.
     */
    public function attachments()
    {
        return $this->hasMany(PostAttachment::class)->orderBy('order');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update storage when post is created, updated, or deleted
        static::saved(function ($post) {
            if ($post->site_id && $post->site) {
                try {
                    // Recalculate storage asynchronously to avoid blocking the request
                    dispatch(function() use ($post) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($post->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    // Silently fail to avoid breaking the request
                    \Log::warning('Failed to update storage for post: ' . $e->getMessage());
                }
            }
        });

        static::deleted(function ($post) {
            if ($post->site_id && $post->site) {
                try {
                    dispatch(function() use ($post) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($post->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for deleted post: ' . $e->getMessage());
                }
            }
        });
    }

    /**
     * Get top-level comments (not replies).
     */
    public function topLevelComments()
    {
        return $this->comments()->whereNull('parent_id');
    }

    /**
     * Get the post that this post is a reply to.
     */
    public function parentPost()
    {
        return $this->belongsTo(Post::class, 'reply_to');
    }

    /**
     * Get the replies to this post.
     */
    public function replies()
    {
        return $this->hasMany(Post::class, 'reply_to')->orderBy('created_at', 'asc');
    }

    /**
     * Increment views count.
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Scope a query to only include pinned posts.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope a query to only include notice posts.
     */
    public function scopeNotice($query)
    {
        return $query->where('is_notice', true);
    }

    /**
     * Scope a query to order posts.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_pinned', 'desc')
            ->orderBy('is_notice', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the content with auto-linked URLs.
     */
    public function getContentWithLinksAttribute()
    {
        $content = $this->content;
        
        // 게시판에서 링크 삭제가 활성화되어 있으면 모든 링크와 URL 텍스트 완전히 제거
        if ($this->board && $this->board->remove_links) {
            // 1. <a> 태그와 그 내용을 완전히 제거 (여러 번 반복하여 중첩된 링크도 제거)
            $oldContent = '';
            while ($oldContent !== $content) {
                $oldContent = $content;
                $content = preg_replace('/<a\s+[^>]*>.*?<\/a>/is', '', $content);
            }
            
            // 2. HTML 태그를 임시로 마스킹하여 보호
            $tagPlaceholders = [];
            $counter = 0;
            $content = preg_replace_callback('/<[^>]+>/is', function($matches) use (&$tagPlaceholders, &$counter) {
                $placeholder = "___HTML_TAG_{$counter}___";
                $tagPlaceholders[$placeholder] = $matches[0];
                $counter++;
                return $placeholder;
            }, $content);
            
            // 3. 텍스트 노드에서 URL 패턴들을 모두 제거
            // HTML 태그 사이의 텍스트 부분만 처리
            $content = preg_replace_callback('/>([^<]+)</', function($matches) {
                $text = $matches[1];
                
                // URL 패턴들을 제거
                $patterns = [
                    // http:// 또는 https://로 시작하는 URL
                    '/(https?:\/\/[^\s<>"\'{}|\\^`\[\]\n\r]+)/i',
                    // www.로 시작하는 URL
                    '/(www\.[^\s<>"\'{}|\\^`\[\]\n\r]+)/i',
                    // 도메인 형식 (naver.com, t.me/tcn_event 등)
                    '/([a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.(?:[a-zA-Z]{2,})(?:\/[^\s<>"\'{}|\\^`\[\]\n\r]*)?)/i',
                ];
                
                foreach ($patterns as $pattern) {
                    $text = preg_replace($pattern, '', $text);
                }
                
                // 연속된 공백 정리
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);
                
                return '>' . $text . '<';
            }, $content);
            
            // 4. 태그 밖의 텍스트도 처리 (HTML 시작 전이나 끝난 후)
            $patterns = [
                '/(https?:\/\/[^\s<>"\'{}|\\^`\[\]\n\r]+)/i',
                '/(www\.[^\s<>"\'{}|\\^`\[\]\n\r]+)/i',
                '/([a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.(?:[a-zA-Z]{2,})(?:\/[^\s<>"\'{}|\\^`\[\]\n\r]*)?)/i',
            ];
            
            foreach ($patterns as $pattern) {
                $oldContent = '';
                while ($oldContent !== $content) {
                    $oldContent = $content;
                    $content = preg_replace($pattern, '', $content);
                }
            }
            
            // 5. HTML 태그 복원
            foreach (array_reverse($tagPlaceholders) as $placeholder => $original) {
                $content = str_replace($placeholder, $original, $content);
            }
            
            // 6. 빈 줄과 연속된 공백 정리
            $content = preg_replace('/\n\s*\n/', "\n", $content);
            $content = preg_replace('/[ \t]+/', ' ', $content);
            $content = trim($content);
        } else {
            // 링크 삭제가 비활성화되어 있으면 자동 링크 변환
            $content = \App\Helpers\TextHelper::autoLinkHtml($content);
        }
        
        return $content;
    }

    /**
     * Get the topics that belong to this post.
     */
    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'post_topic');
    }

    /**
     * Get the likes for the post.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get the saved posts for the post.
     */
    public function savedPosts()
    {
        if (Schema::hasTable('saved_posts')) {
            return $this->hasMany(SavedPost::class);
        }
        return $this->hasMany(SavedPost::class)->whereRaw('1 = 0'); // 빈 관계 반환
    }

    /**
     * Check if the post is saved by a user.
     */
    public function isSavedByUser($userId)
    {
        if (!Schema::hasTable('saved_posts') || !$userId) {
            return false;
        }
        return $this->savedPosts()->where('user_id', $userId)->exists();
    }

    /**
     * Get the event options for this post (for quiz events).
     */
    public function eventOptions()
    {
        return $this->hasMany(EventOption::class)->orderBy('order');
    }

    /**
     * Get the event participants for this post.
     */
    public function eventParticipants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Check if this post is an event post.
     */
    public function isEventPost()
    {
        return $this->board && $this->board->type === Board::TYPE_EVENT;
    }

    /**
     * Get the event status (ongoing, ended, upcoming).
     */
    public function getEventStatusAttribute()
    {
        if (!$this->isEventPost()) {
            return null;
        }

        // 수동 종료 체크
        if ($this->event_is_ended) {
            return 'ended';
        }

        // 날짜 기반 자동 종료 체크 (당일 이벤트는 종료되지 않음)
        $now = now();
        
        // 시작일 체크
        if ($this->event_start_date && !$this->event_end_undecided) {
            $startDate = $this->event_start_date instanceof \Carbon\Carbon 
                ? $this->event_start_date->copy()->startOfDay()
                : \Carbon\Carbon::parse($this->event_start_date)->startOfDay();
            $today = $now->copy()->startOfDay();
            
            if ($today->lt($startDate)) {
                return 'upcoming';
            }
        }
        
        // 종료일 체크 (당일 이벤트는 종료되지 않음)
        if ($this->event_end_date && !$this->event_end_undecided) {
            $endDate = $this->event_end_date instanceof \Carbon\Carbon 
                ? $this->event_end_date->copy()->startOfDay()
                : \Carbon\Carbon::parse($this->event_end_date)->startOfDay();
            $today = $now->copy()->startOfDay();
            
            // 오늘이 종료일보다 큰 경우에만 종료 (같으면 진행중)
            // 예: 오늘이 11월 25일이고 종료일이 11월 25일이면 종료되지 않음
            if ($today->isAfter($endDate)) {
                return 'ended';
            }
        }

        if ($this->event_end_undecided) {
            return 'ongoing';
        }

        // 시작일과 종료일이 모두 있는 경우
        if ($this->event_start_date && $this->event_end_date) {
            $startDate = $this->event_start_date instanceof \Carbon\Carbon 
                ? $this->event_start_date->copy()->startOfDay()
                : \Carbon\Carbon::parse($this->event_start_date)->startOfDay();
            $endDate = $this->event_end_date instanceof \Carbon\Carbon 
                ? $this->event_end_date->copy()->startOfDay()
                : \Carbon\Carbon::parse($this->event_end_date)->startOfDay();
            $today = $now->copy()->startOfDay();
            
            // 오늘이 시작일과 종료일 사이에 있거나 같으면 진행중
            if ($today->gte($startDate) && $today->lte($endDate)) {
                return 'ongoing';
            }
        }

        return 'ongoing';
    }

    /**
     * Get the like count for the post.
     */
    public function getLikeCountAttribute()
    {
        try {
            if (!Schema::hasTable('post_likes')) {
                return 0;
            }
            // 이미 eager load된 관계가 있으면 그것을 직접 사용
            if ($this->relationLoaded('likes')) {
                if ($this->likes !== null && $this->likes instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $this->likes->filter(function($like) {
                        return $like->type === 'like';
                    })->count();
                }
            }
            // 그렇지 않으면 쿼리 실행
            return $this->likes()->where('type', 'like')->count();
        } catch (\Exception $e) {
            \Log::error('getLikeCountAttribute error', ['error' => $e->getMessage(), 'post_id' => $this->id]);
            return 0;
        }
    }

    /**
     * Get the dislike count for the post.
     */
    public function getDislikeCountAttribute()
    {
        try {
            if (!Schema::hasTable('post_likes')) {
                return 0;
            }
            // 이미 eager load된 관계가 있으면 그것을 직접 사용
            if ($this->relationLoaded('likes') && $this->likes !== null) {
                if ($this->likes instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $this->likes->filter(function($like) {
                        return $like->type === 'dislike';
                    })->count();
                }
            }
            // 그렇지 않으면 쿼리 실행
            return $this->likes()->where('type', 'dislike')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if a user has liked/disliked this post.
     */
    public function hasUserLike($userId)
    {
        try {
            if (!\Schema::hasTable('post_likes')) {
                return null;
            }
            if (!$userId) {
                return null;
            }
            // 이미 eager load된 관계가 있으면 그것을 직접 사용
            if ($this->relationLoaded('likes') && $this->likes !== null) {
                if ($this->likes instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $this->likes->firstWhere('user_id', $userId);
                }
            }
            // 그렇지 않으면 쿼리 실행
            return $this->likes()->where('user_id', $userId)->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}

