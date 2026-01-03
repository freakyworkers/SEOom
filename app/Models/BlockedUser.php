<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'blocker_id',
        'blocked_user_id',
        'blocked_guest_session_id',
        'blocked_nickname',
    ];

    /**
     * Get the site that owns the block.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user who blocked.
     */
    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    /**
     * Get the blocked user.
     */
    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    /**
     * Check if a user is blocked by another user.
     */
    public static function isBlocked(int $siteId, int $blockerId, ?int $blockedUserId = null, ?string $blockedGuestSessionId = null): bool
    {
        $query = self::where('site_id', $siteId)
            ->where('blocker_id', $blockerId);
        
        if ($blockedUserId) {
            $query->where('blocked_user_id', $blockedUserId);
        } elseif ($blockedGuestSessionId) {
            $query->where('blocked_guest_session_id', $blockedGuestSessionId);
        } else {
            return false;
        }
        
        return $query->exists();
    }
}





