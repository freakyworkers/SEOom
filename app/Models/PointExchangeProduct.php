<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointExchangeProduct extends Model
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
        'total_amount',
        'order',
    ];

    protected $casts = [
        'pending_count' => 'integer',
        'completed_count' => 'integer',
        'rejected_count' => 'integer',
        'total_count' => 'integer',
        'total_amount' => 'integer',
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
     * Get the applications for the product.
     */
    public function applications()
    {
        return $this->hasMany(PointExchangeApplication::class, 'product_id');
    }

    /**
     * Update statistics.
     */
    public function updateStatistics()
    {
        $this->pending_count = $this->applications()->where('status', 'pending')->count();
        $this->completed_count = $this->applications()->where('status', 'completed')->count();
        $this->rejected_count = $this->applications()->where('status', 'rejected')->count();
        $this->total_count = $this->applications()->count();
        $this->total_amount = $this->applications()->where('status', 'completed')->sum('points');
        $this->save();
    }
}







