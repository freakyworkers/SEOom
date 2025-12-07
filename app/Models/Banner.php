<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SiteUsageService;
use Illuminate\Support\Facades\Storage;

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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When banner image is updated, update storage
        static::saved(function ($banner) {
            if ($banner->site_id && $banner->image_path) {
                $filePath = storage_path('app/public/' . $banner->image_path);
                if (file_exists($filePath)) {
                    $fileSize = filesize($filePath);
                    // Only update if this is a new image (not just an update)
                    if ($banner->wasRecentlyCreated || $banner->wasChanged('image_path')) {
                        // If image_path changed, subtract old size
                        if ($banner->wasChanged('image_path') && $banner->getOriginal('image_path')) {
                            $oldPath = storage_path('app/public/' . $banner->getOriginal('image_path'));
                            if (file_exists($oldPath)) {
                                app(SiteUsageService::class)->updateStorage(
                                    $banner->site_id,
                                    -filesize($oldPath)
                                );
                            }
                        }
                        // Add new size
                        app(SiteUsageService::class)->updateStorage($banner->site_id, $fileSize);
                    }
                }
            }
        });

        // When banner is deleted, subtract from storage
        static::deleted(function ($banner) {
            if ($banner->site_id && $banner->image_path) {
                $filePath = storage_path('app/public/' . $banner->image_path);
                if (file_exists($filePath)) {
                    app(SiteUsageService::class)->updateStorage(
                        $banner->site_id,
                        -filesize($filePath)
                    );
                }
            }
        });
    }

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

