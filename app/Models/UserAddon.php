<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_id',
        'addon_product_id',
        'subscription_id',
        'amount_mb',
        'price',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'amount_mb' => 'integer',
        'price' => 'decimal:2',
        'expires_at' => 'date',
    ];

    /**
     * Get the user that owns this addon.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the site that owns this addon.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the addon product.
     */
    public function addonProduct()
    {
        return $this->belongsTo(AddonProduct::class);
    }

    /**
     * Get the subscription.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if addon is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false; // 일회성 구매는 만료 없음
        }
        return $this->expires_at->isPast();
    }

    /**
     * Get type from addon product.
     */
    public function getTypeAttribute(): string
    {
        return $this->addonProduct->type ?? 'storage';
    }
}




