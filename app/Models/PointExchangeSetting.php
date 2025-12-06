<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointExchangeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'page_title',
        'notice_title',
        'notices',
        'min_amount',
        'max_amount',
        'form_fields',
        'requirements',
        'random_order',
        'products_per_page',
        'pc_columns',
        'mobile_columns',
    ];

    protected $casts = [
        'notices' => 'array',
        'form_fields' => 'array',
        'requirements' => 'array',
        'min_amount' => 'integer',
        'max_amount' => 'integer',
        'random_order' => 'boolean',
        'products_per_page' => 'integer',
        'pc_columns' => 'integer',
        'mobile_columns' => 'integer',
    ];

    /**
     * Get the site that owns the setting.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get or create setting for site.
     */
    public static function getForSite($siteId)
    {
        return static::firstOrCreate(
            ['site_id' => $siteId],
            [
                'page_title' => '포인트교환',
                'notice_title' => '포인트 교환 필독 사항 안내',
                'min_amount' => 10000,
                'max_amount' => 100000,
                'random_order' => false,
                'products_per_page' => 12,
                'pc_columns' => 4,
                'mobile_columns' => 2,
            ]
        );
    }
}


