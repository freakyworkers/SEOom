<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 기존 플랜 삭제 (외래 키 제약 조건 때문에 truncate 대신 delete 사용)
        // 외래 키 체크를 일시적으로 비활성화
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('plans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // 통일된 제한사항 (Free와 Basic 공통)
        $baseLimits = json_encode([
            'boards' => 2,
            'widgets' => 3,
            'custom_pages' => 2,
            'users' => 20,
            'storage' => 512, // 512MB
        ]);
        $baseTraffic = 2560; // 2.5GB
        
        // 각 타입별 기본 기능 정의
        $landingFeatures = [
            'main_features' => [
                'dashboard',
                'users',
                'boards',
                'posts',
                'banners',
                'settings',
                'main_widgets',
                'sidebar_widgets',
                'custom_pages',
                'toggle_menus',
            ],
            'board_types' => [
                'general',
                'classic',
            ],
            'registration_features' => [],
            'sidebar_widget_types' => [],
            'main_widget_types' => [
                'image', 'custom_html', 'block', 'block_slide',
            ],
            'custom_page_widget_types' => [
                'image', 'custom_html', 'block', 'block_slide',
            ],
        ];
        
        $brandFeatures = [
            'main_features' => [
                'dashboard',
                'users',
                'registration_settings',
                'mail_settings',
                'contact_forms',
                'maps',
                'user_ranks',
                'boards',
                'posts',
                'attendance',
                'menus',
                'banners',
                'settings',
                'sidebar_widgets',
                'main_widgets',
                'custom_pages',
                'toggle_menus',
            ],
            'board_types' => [
                'general',
                'classic',
                'photo',
                'blog',
            ],
            'registration_features' => [
                'signup_points',
                'phone_verification',
            ],
            'sidebar_widget_types' => [
                'recent_posts', 'popular_posts', 'board_list', 'board', 'custom_html', 'tab_menu',
                'user_ranking', 'image', 'image_slide', 'contact_form', 'map', 'search',
            ],
            'main_widget_types' => [
                'recent_posts', 'popular_posts', 'board_list', 'board', 'marquee_board', 'gallery',
                'custom_html', 'tab_menu', 'user_ranking', 'block', 'block_slide', 'image',
                'image_slide', 'contact_form', 'map',
            ],
            'custom_page_widget_types' => [
                'recent_posts', 'popular_posts', 'board_list', 'board', 'marquee_board', 'gallery',
                'custom_html', 'tab_menu', 'user_ranking', 'block', 'block_slide', 'image',
                'image_slide', 'contact_form', 'map',
            ],
        ];
        
        $communityFeatures = [
            'main_features' => [
                'dashboard',
                'users',
                'registration_settings',
                'mail_settings',
                'contact_forms',
                'maps',
                'crawlers',
                'user_ranks',
                'boards',
                'posts',
                'attendance',
                'point_exchange',
                'event_application',
                'menus',
                'messages',
                'banners',
                'popups',
                'blocked_ips',
                'custom_code',
                'settings',
                'sidebar_widgets',
                'main_widgets',
                'custom_pages',
                'toggle_menus',
            ],
            'board_types' => [
                'general',
                'photo',
                'bookmark',
                'blog',
                'classic',
                'instagram',
                'event',
                'one_on_one',
                'pinterest',
            ],
            'registration_features' => [
                'signup_points',
                'phone_verification',
                'identity_verification',
                'referrer',
            ],
            'sidebar_widget_types' => [
                'recent_posts', 'popular_posts', 'weekly_popular_posts', 'monthly_popular_posts',
                'board_list', 'board', 'marquee_board', 'gallery', 'search', 'custom_html',
                'tab_menu', 'user_ranking', 'block', 'block_slide', 'image', 'image_slide',
                'contact_form', 'map',
            ],
            'main_widget_types' => [
                'recent_posts', 'popular_posts', 'weekly_popular_posts', 'monthly_popular_posts',
                'board_list', 'board', 'marquee_board', 'gallery', 'search', 'custom_html',
                'tab_menu', 'user_ranking', 'block', 'block_slide', 'image', 'image_slide',
                'contact_form', 'map',
            ],
            'custom_page_widget_types' => [
                'recent_posts', 'popular_posts', 'weekly_popular_posts', 'monthly_popular_posts',
                'board_list', 'board', 'marquee_board', 'gallery', 'search', 'custom_html',
                'tab_menu', 'user_ranking', 'block', 'block_slide', 'image', 'image_slide',
                'contact_form', 'map',
            ],
        ];
        
        // 플랜 정의 (타입별로 5단계)
        $planTypes = [
            'landing' => [
                'name' => '랜딩페이지',
                'features' => $landingFeatures,
            ],
            'brand' => [
                'name' => '브랜드',
                'features' => $brandFeatures,
            ],
            'community' => [
                'name' => '커뮤니티',
                'features' => $communityFeatures,
            ],
        ];
        
        $tiers = [
            'free' => [
                'name_suffix' => '무료',
                'billing_type' => 'free',
                'price' => 0,
                'one_time_price' => null,
                'storage' => 512, // MB
                'traffic' => 2560, // MB (2.5GB)
                'can_change_domain' => false,
                'sort_order' => 1,
            ],
            'basic' => [
                'name_suffix' => '베이직',
                'billing_type' => 'monthly',
                'price' => 10000, // 월 10,000원
                'one_time_price' => null,
                'storage' => 512, // MB
                'traffic' => 2560, // MB (2.5GB)
                'can_change_domain' => true,
                'sort_order' => 2,
            ],
            'standard' => [
                'name_suffix' => '스탠다드',
                'billing_type' => 'monthly',
                'price' => 20000, // 월 20,000원
                'one_time_price' => null,
                'storage' => 2048, // MB (2GB)
                'traffic' => 10240, // MB (10GB)
                'can_change_domain' => true,
                'sort_order' => 3,
            ],
            'business' => [
                'name_suffix' => '비지니스',
                'billing_type' => 'monthly',
                'price' => 40000, // 월 40,000원
                'one_time_price' => null,
                'storage' => 5120, // MB (5GB)
                'traffic' => 20480, // MB (20GB)
                'can_change_domain' => true,
                'sort_order' => 4,
            ],
            'pro' => [
                'name_suffix' => '프로',
                'billing_type' => 'monthly',
                'price' => 80000, // 월 80,000원
                'one_time_price' => null,
                'storage' => 10240, // MB (10GB)
                'traffic' => 51200, // MB (50GB)
                'can_change_domain' => true,
                'sort_order' => 5,
            ],
        ];
        
        // 각 타입별로 5단계 플랜 생성
        foreach ($planTypes as $type => $typeData) {
            foreach ($tiers as $tier => $tierData) {
                $limits = json_decode($baseLimits, true);
                $limits['storage'] = $tierData['storage'];
                
                DB::table('plans')->insert([
                    'name' => $typeData['name'] . ' ' . $tierData['name_suffix'],
                    'slug' => $type . '-' . $tier,
                    'description' => $typeData['name'] . ' 플랜의 ' . $tierData['name_suffix'] . ' 버전입니다.',
                    'type' => $type,
                    'price' => $tierData['price'],
                    'billing_type' => $tierData['billing_type'],
                    'one_time_price' => $tierData['one_time_price'],
                    'features' => json_encode($typeData['features']),
                    'limits' => json_encode($limits),
                    'traffic_limit_mb' => $tierData['traffic'],
                    'sort_order' => $tierData['sort_order'],
                    'is_active' => true,
                    'is_default' => ($tier === 'free' && $type === 'landing'), // 랜딩페이지 무료만 기본
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 모든 플랜 삭제 (외래 키 제약 조건 때문에 truncate 대신 delete 사용)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('plans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};


