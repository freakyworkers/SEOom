<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plugin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_type',
        'one_time_price',
        'features',
        'is_active',
        'sort_order',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'one_time_price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get plugin purchases
     */
    public function purchases()
    {
        return $this->hasMany(PluginPurchase::class);
    }

    /**
     * Check if plugin is purchased by site
     */
    public function isPurchasedBySite($siteId)
    {
        return $this->purchases()
            ->where('site_id', $siteId)
            ->where('status', 'active')
            ->exists();
    }
}

