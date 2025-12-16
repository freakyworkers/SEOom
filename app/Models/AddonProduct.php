<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddonProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'type',
        'amount_mb',
        'price',
        'billing_cycle',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount_mb' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get user addons for this product.
     */
    public function userAddons()
    {
        return $this->hasMany(UserAddon::class);
    }

    /**
     * Get options for this product.
     */
    public function options()
    {
        return $this->hasMany(AddonProductOption::class)->orderBy('sort_order');
    }

    /**
     * Get active options for this product.
     */
    public function activeOptions()
    {
        return $this->options()->where('is_active', true);
    }

    /**
     * Get thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return null;
        }
        return asset('storage/' . $this->thumbnail);
    }

    /**
     * Get formatted amount (GB).
     */
    public function getAmountGbAttribute(): float
    {
        return round($this->amount_mb / 1024, 2);
    }

    /**
     * Get formatted price per GB.
     */
    public function getPricePerGbAttribute(): float
    {
        if ($this->amount_mb <= 0) {
            return 0;
        }
        return round(($this->price / $this->amount_mb) * 1024, 2);
    }

    /**
     * Get type name in Korean.
     */
    public function getTypeNameKoAttribute(): string
    {
        $typeMap = [
            'storage' => '저장 용량',
            'traffic' => '트래픽',
            'feature_crawler' => '크롤러',
            'feature_event_application' => '신청형 이벤트',
            'feature_point_exchange' => '포인트 교환',
            'board_type_event' => '이벤트',
            'registration_referral' => '추천인 기능',
            'feature_point_message' => '포인트 쪽지',
        ];

        return $typeMap[$this->type] ?? $this->type;
    }
}

