<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SidebarWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'type',
        'title',
        'settings',
        'order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the site that owns the widget.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get available widget types.
     */
    public static function getAvailableTypes(): array
    {
        return [
            'recent_posts' => '최근 게시글',
            'popular_posts' => '인기 게시글',
            'weekly_popular_posts' => '주간 인기글',
            'monthly_popular_posts' => '월간 인기글',
            'board_list' => '게시판 목록',
            'board' => '게시판',
            'marquee_board' => '게시글 전광판',
            'gallery' => '갤러리',
            'search' => '검색',
            'custom_html' => '커스텀 HTML',
            'tab_menu' => '탭메뉴',
            'toggle_menu' => '토글 메뉴',
            'user_ranking' => '회원 랭킹',
            'block' => '블록',
            'block_slide' => '블록 슬라이드',
            'image' => '이미지',
            'image_slide' => '이미지 슬라이드',
            'contact_form' => '컨텍트폼',
            'map' => '지도',
            'plans' => '요금제',
            'chat' => '채팅',
            'create_site' => '사이트 생성 (마스터 전용)',
            'countdown' => '카운트다운',
        ];
    }
}

