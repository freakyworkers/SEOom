<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'event_option_id',
        'points_awarded',
        'is_correct',
    ];

    protected $casts = [
        'points_awarded' => 'integer',
        'is_correct' => 'boolean',
    ];

    /**
     * Get the post that owns this participant.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that owns this participant.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event option that this participant selected.
     */
    public function eventOption()
    {
        return $this->belongsTo(EventOption::class);
    }
}







