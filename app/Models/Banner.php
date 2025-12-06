<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'location',
        'type',
        'image_path',
        'html_code',
        'link',
        'open_new_window',
        'order',
        'is_pinned_top',
        'pinned_position',
    ];

    protected $casts = [
        'open_new_window' => 'boolean',
        'order' => 'integer',
        'is_pinned_top' => 'boolean',
        'pinned_position' => 'integer',
    ];

    /**
     * Get the site that owns the banner.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}

