<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'guest_session_id',
        'nickname',
        'message',
        'attachment_path',
        'attachment_type',
    ];

    /**
     * Get the site that owns the chat message.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user who sent the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guest session.
     */
    public function guestSession()
    {
        return $this->belongsTo(ChatGuestSession::class, 'guest_session_id', 'session_id');
    }

    /**
     * Check if message is from a guest.
     */
    public function isGuest(): bool
    {
        return $this->user_id === null;
    }
}






