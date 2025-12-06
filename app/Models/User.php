<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Site;
use App\Models\UserRank;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'username',
        'nickname',
        'email',
        'phone',
        'postal_code',
        'address',
        'address_detail',
        'password',
        'role',
        'avatar',
        'points',
        'referrer_id',
        'last_login_ip',
        'provider',
        'provider_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string',
        'points' => 'integer',
    ];

    /**
     * Get the site that owns the user.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the referrer (user who referred this user).
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get users referred by this user.
     */
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the attendances for the user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get posts count attribute.
     */
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }

    /**
     * Get comments count attribute.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager.
     */
    public function isManager()
    {
        return $this->role === 'manager' || $this->isAdmin();
    }

    /**
     * Check if user can manage site.
     */
    public function canManage()
    {
        return $this->isManager();
    }

    /**
     * Add points to user.
     */
    public function addPoints($points)
    {
        $this->points += $points;
        $this->save();
        return $this;
    }

    /**
     * Subtract points from user.
     */
    public function subtractPoints($points)
    {
        $this->points = max(0, $this->points - $points);
        $this->save();
        return $this;
    }

    /**
     * Get messages count attribute.
     */
    public function getMessagesCountAttribute()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('messages')) {
            return 0;
        }
        return $this->receivedMessages()->count();
    }

    /**
     * Get user rank based on criteria.
     */
    public function getUserRank($siteId = null)
    {
        $siteId = $siteId ?? $this->site_id;
        
        if (!$siteId || !\Illuminate\Support\Facades\Schema::hasTable('user_ranks')) {
            return null;
        }

        // 관리자/매니저인 경우 null 반환 (특별 아이콘 사용)
        if ($this->isAdmin() || $this->isManager()) {
            return null;
        }

        $site = Site::find($siteId);
        if (!$site) {
            return null;
        }

        $criteriaType = $site->getSetting('rank_criteria_type', 'current_points');
        $ranks = UserRank::where('site_id', $siteId)
            ->where('criteria_type', $criteriaType)
            ->orderBy('criteria_value', 'desc')
            ->get();

        if ($ranks->isEmpty()) {
            return null;
        }

        // 사용자의 기준 값 계산
        $userValue = 0;
        if ($criteriaType === 'current_points') {
            $userValue = $this->points ?? 0;
        } elseif ($criteriaType === 'max_points') {
            $userValue = $this->points ?? 0; // max_points는 현재 포인트와 동일하게 처리
        } elseif ($criteriaType === 'post_count') {
            $userValue = $this->posts()->count();
        }

        // 기준 값 이상인 등급 중 가장 높은 등급 찾기
        foreach ($ranks as $rank) {
            if ($userValue >= $rank->criteria_value) {
                return $rank;
            }
        }

        // 기준 값 미만인 경우 가장 낮은 등급 반환
        return $ranks->last();
    }
}

