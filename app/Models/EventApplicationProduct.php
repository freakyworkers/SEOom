<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventApplicationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'thumbnail_path',
        'item_name',
        'item_content',
        'notice',
        'pending_count',
        'completed_count',
        'rejected_count',
        'total_count',
        'order',
    ];

    protected $casts = [
        'pending_count' => 'integer',
        'completed_count' => 'integer',
        'rejected_count' => 'integer',
        'total_count' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the site that owns the product.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the submissions for the product.
     */
    public function submissions()
    {
        return $this->hasMany(EventApplicationSubmission::class, 'product_id');
    }

    /**
     * Update statistics.
     */
    public function updateStatistics()
    {
        $this->pending_count = $this->submissions()->where('status', 'pending')->count();
        $this->completed_count = $this->submissions()->where('status', 'completed')->count();
        $this->rejected_count = $this->submissions()->where('status', 'rejected')->count();
        $this->total_count = $this->submissions()->count();
        $this->save();
    }
}








