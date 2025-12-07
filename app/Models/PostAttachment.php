<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SiteUsageService;

class PostAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When attachment is created, add to storage
        static::created(function ($attachment) {
            if ($attachment->post && $attachment->post->site_id && $attachment->file_size) {
                app(SiteUsageService::class)->updateStorage(
                    $attachment->post->site_id,
                    $attachment->file_size
                );
            }
        });

        // When attachment is deleted, subtract from storage
        static::deleted(function ($attachment) {
            if ($attachment->post && $attachment->post->site_id && $attachment->file_size) {
                app(SiteUsageService::class)->updateStorage(
                    $attachment->post->site_id,
                    -$attachment->file_size
                );
            }
        });
    }

    /**
     * Get the post that owns the attachment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the file URL.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Check if file is an image.
     */
    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}







