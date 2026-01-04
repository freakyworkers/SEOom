<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'board_id',
        'name',
        'color',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Get the board that owns the topic.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the posts that belong to this topic.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_topic');
    }

    /**
     * Scope a query to order topics by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}










