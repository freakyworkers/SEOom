<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'sender_id',
        'receiver_id',
        'content',
        'is_read',
        'parent_id',
        'points',
        'points_received',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'points_received' => 'boolean',
    ];

    /**
     * Get the site that owns the message.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the parent message (for replies).
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Get replies to this message.
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
    }

    /**
     * Get unread messages count for user.
     */
    public static function getUnreadCount($userId, $siteId = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('messages')) {
            return 0;
        }
        
        $query = self::where('receiver_id', $userId)
            ->where('is_read', false);
        
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        return $query->count();
    }
}

