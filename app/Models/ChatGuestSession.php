<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGuestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'session_id',
        'ip_address',
        'user_agent',
        'guest_number',
    ];

    /**
     * Get the site that owns the guest session.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the next guest number for a site.
     */
    public static function getNextGuestNumber(int $siteId): int
    {
        $lastGuest = self::where('site_id', $siteId)
            ->orderBy('guest_number', 'desc')
            ->first();
        
        return $lastGuest ? $lastGuest->guest_number + 1 : 1;
    }

    /**
     * Get or create guest session.
     */
    public static function getOrCreate(string $sessionId, int $siteId, string $ipAddress = null, string $userAgent = null): self
    {
        $guestSession = self::where('session_id', $sessionId)
            ->where('site_id', $siteId)
            ->first();
        
        if (!$guestSession) {
            $guestNumber = self::getNextGuestNumber($siteId);
            $guestSession = self::create([
                'site_id' => $siteId,
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'guest_number' => $guestNumber,
            ]);
        }
        
        return $guestSession;
    }

    /**
     * Get guest nickname.
     */
    public function getNickname(): string
    {
        return '게스트' . $this->guest_number;
    }
}


