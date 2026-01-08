<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'option_text',
        'order',
        'is_correct',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_correct' => 'boolean',
    ];

    /**
     * Get the post that owns this option.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the participants who selected this option.
     */
    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Get the participant count for this option.
     */
    public function getParticipantCountAttribute()
    {
        return $this->participants()->count();
    }
}











