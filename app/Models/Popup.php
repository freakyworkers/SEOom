<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SiteUsageService;

class Popup extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'type',
        'image_path',
        'html_code',
        'link',
        'open_new_window',
        'display_type', // 'overlay' (겹치기), 'list' (나열하기)
        'position', // 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'
        'target_type', // 'all', 'main', 'board'
        'target_board_id', // 게시판/페이지 ID (target_type이 'board'일 때)
        'order',
        'is_active',
    ];

    protected $casts = [
        'open_new_window' => 'boolean',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When popup image is updated, update storage
        static::saved(function ($popup) {
            if ($popup->site_id && $popup->image_path) {
                $filePath = storage_path('app/public/' . $popup->image_path);
                if (file_exists($filePath)) {
                    $fileSize = filesize($filePath);
                    // Only update if this is a new image (not just an update)
                    if ($popup->wasRecentlyCreated || $popup->wasChanged('image_path')) {
                        // If image_path changed, subtract old size
                        if ($popup->wasChanged('image_path') && $popup->getOriginal('image_path')) {
                            $oldPath = storage_path('app/public/' . $popup->getOriginal('image_path'));
                            if (file_exists($oldPath)) {
                                app(SiteUsageService::class)->updateStorage(
                                    $popup->site_id,
                                    -filesize($oldPath)
                                );
                            }
                        }
                        // Add new size
                        app(SiteUsageService::class)->updateStorage($popup->site_id, $fileSize);
                    }
                }
            }
        });

        // When popup is deleted, subtract from storage
        static::deleted(function ($popup) {
            if ($popup->site_id && $popup->image_path) {
                $filePath = storage_path('app/public/' . $popup->image_path);
                if (file_exists($filePath)) {
                    app(SiteUsageService::class)->updateStorage(
                        $popup->site_id,
                        -filesize($filePath)
                    );
                }
            }
        });
    }

    /**
     * Get the site that owns the popup.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the board that this popup targets (if applicable).
     */
    public function targetBoard()
    {
        // target_id가 있을 때만 Board 모델과 연결
        return $this->belongsTo(Board::class, 'target_id');
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}

