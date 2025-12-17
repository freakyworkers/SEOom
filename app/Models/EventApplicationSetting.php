<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventApplicationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'page_title',
        'notice_title',
        'notices',
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
                'page_title' => '신청형 이벤트',
                'notice_title' => '신청형 이벤트 필독 사항 안내',
                'random_order' => false,
                'products_per_page' => 12,
                'pc_columns' => 4,
                'mobile_columns' => 2,
            ]
        );
    }
}





