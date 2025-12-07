<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'location',
        'code',
    ];

    /**
     * Get the site that owns the custom code.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get custom code by location for a site.
     */
    public static function getByLocation($siteId, $location)
    {
        return self::where('site_id', $siteId)
            ->where('location', $location)
            ->first();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update storage when custom code is created, updated, or deleted
        static::saved(function ($customCode) {
            if ($customCode->site_id && $customCode->site) {
                try {
                    dispatch(function() use ($customCode) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($customCode->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for custom code: ' . $e->getMessage());
                }
            }
        });

        static::deleted(function ($customCode) {
            if ($customCode->site_id && $customCode->site) {
                try {
                    dispatch(function() use ($customCode) {
                        app(\App\Services\SiteUsageService::class)->recalculateStorage($customCode->site);
                    })->afterResponse();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update storage for deleted custom code: ' . $e->getMessage());
                }
            }
        });
    }
}




