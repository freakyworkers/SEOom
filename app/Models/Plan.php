<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'price',
        'billing_type',
        'one_time_price',
        'features',
        'limits',
        'sort_order',
        'is_active',
        'is_default',
        'traffic_limit_mb',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'one_time_price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get main features (대 메뉴 기능들)
     */
    public function getMainFeaturesAttribute()
    {
        return $this->features['main_features'] ?? [];
    }

    /**
     * Get board types (게시판 타입들)
     */
    public function getBoardTypesAttribute()
    {
        return $this->features['board_types'] ?? [];
    }

    /**
     * Get registration features (회원가입 세부 기능들)
     */
    public function getRegistrationFeaturesAttribute()
    {
        return $this->features['registration_features'] ?? [];
    }

    /**
     * Get sidebar widget types (사이드바 위젯 타입들)
     */
    public function getSidebarWidgetTypesAttribute()
    {
        return $this->features['sidebar_widget_types'] ?? [];
    }

    /**
     * Get main widget types (메인 위젯 타입들)
     */
    public function getMainWidgetTypesAttribute()
    {
        return $this->features['main_widget_types'] ?? [];
    }

    /**
     * Get custom page widget types (커스텀 페이지 위젯 타입들)
     */
    public function getCustomPageWidgetTypesAttribute()
    {
        return $this->features['custom_page_widget_types'] ?? [];
    }

    /**
     * Check if plan has a main feature
     */
    public function hasMainFeature(string $feature): bool
    {
        $mainFeatures = $this->main_features;
        return in_array($feature, $mainFeatures);
    }

    /**
     * Check if plan has a board type
     */
    public function hasBoardType(string $type): bool
    {
        $boardTypes = $this->board_types;
        return in_array($type, $boardTypes);
    }

    /**
     * Check if plan has a registration feature
     */
    public function hasRegistrationFeature(string $feature): bool
    {
        $regFeatures = $this->registration_features;
        return in_array($feature, $regFeatures);
    }

    /**
     * Get sites using this plan.
     */
    public function sites()
    {
        return $this->hasMany(Site::class, 'plan', 'slug');
    }

    /**
     * Check if plan is for landing pages.
     */
    public function isLanding(): bool
    {
        return $this->type === 'landing';
    }

    /**
     * Check if plan is for brand websites.
     */
    public function isBrand(): bool
    {
        return $this->type === 'brand';
    }

    /**
     * Check if plan is for community websites.
     */
    public function isCommunity(): bool
    {
        return $this->type === 'community';
    }

    /**
     * Get plan type name in Korean.
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'landing' => '랜딩페이지',
            'brand' => '브랜드',
            'community' => '커뮤니티',
            'server' => '서버 용량',
            default => '기타',
        };
    }
}
