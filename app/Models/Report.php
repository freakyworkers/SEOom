<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'reporter_id',
        'reporter_guest_session_id',
        'reporter_nickname',
        'reported_user_id',
        'reported_guest_session_id',
        'reported_nickname',
        'report_type',
        'post_id',
        'chat_message_id',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the site that owns the report.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the reporter (user who reported).
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the reported user.
     */
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the reported post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the reported chat message.
     */
    public function chatMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    /**
     * Get the reviewer (admin who reviewed).
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Mark as reviewed.
     */
    public function markAsReviewed(int $reviewerId)
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }
}

