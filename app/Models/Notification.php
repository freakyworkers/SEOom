<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'type',
        'title',
        'content',
        'link',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    /**
     * Get the site that owns the notification.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
    }

    /**
     * Get unread notifications count for user.
     */
    public static function getUnreadCount($userId, $siteId = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return 0;
        }
        
        $query = self::where('user_id', $userId)
            ->where('is_read', false);
        
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        return $query->count();
    }
}

