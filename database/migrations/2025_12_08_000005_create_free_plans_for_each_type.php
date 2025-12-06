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
        // 랜딩페이지 무료 플랜 (랜딩페이지 플랜의 기능을 그대로 유지, 제한사항만 더 엄격)
        $landingFreeExists = DB::table('plans')->where('slug', 'free-landing')->exists();
        if (!$landingFreeExists) {
            DB::table('plans')->insert([
                'name' => '무료 랜딩페이지 플랜',
                'slug' => 'free-landing',
                'description' => '랜딩페이지 플랜의 모든 기능을 무료로 사용할 수 있지만, 제한사항이 더 엄격합니다.',
                'type' => 'landing',
                'price' => 0,
                'billing_type' => 'free',
                'one_time_price' => null,
                'features' => json_encode([
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
                        'image',
                        'custom_html',
                        'block',
                        'block_slide',
                    ],
                    'custom_page_widget_types' => [
                        'image',
                        'custom_html',
                        'block',
                        'block_slide',
                    ],
                ]),
                'limits' => json_encode([
                    'boards' => 2,        // 제한적 (유료 3개에서 2개로)
                    'widgets' => 3,       // 제한적 (유료 5개에서 3개로)
                    'custom_pages' => 2,  // 제한적 (유료 5개에서 2개로)
                    'users' => 20,        // 제한적 (유료 100명에서 20명으로)
                    'storage' => 512,     // 제한적 (유료 1GB에서 512MB로)
                ]),
                'traffic_limit_mb' => 2560, // 제한적 (유료 5GB에서 2.5GB로)
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 브랜드 무료 플랜 (브랜드 플랜의 기능을 그대로 유지, 제한사항만 더 엄격)
        $brandFreeExists = DB::table('plans')->where('slug', 'free-brand')->exists();
        if (!$brandFreeExists) {
            DB::table('plans')->insert([
                'name' => '무료 브랜드 플랜',
                'slug' => 'free-brand',
                'description' => '브랜드 플랜의 모든 기능을 무료로 사용할 수 있지만, 제한사항이 더 엄격합니다.',
                'type' => 'brand',
                'price' => 0,
                'billing_type' => 'free',
                'one_time_price' => null,
                'features' => json_encode([
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
                ]),
                'limits' => json_encode([
                    'boards' => 3,        // 제한적 (유료 10개에서 3개로)
                    'widgets' => 5,       // 제한적 (유료 20개에서 5개로)
                    'custom_pages' => 3,  // 제한적 (유료 20개에서 3개로)
                    'users' => 30,        // 제한적 (유료 1000명에서 30명으로)
                    'storage' => 1024,     // 제한적 (유료 5GB에서 1GB로)
                ]),
                'traffic_limit_mb' => 5120, // 제한적 (유료 10GB에서 5GB로)
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 커뮤니티 무료 플랜 (커뮤니티 플랜의 기능을 그대로 유지, 제한사항만 더 엄격)
        $communityFreeExists = DB::table('plans')->where('slug', 'free-community')->exists();
        if (!$communityFreeExists) {
            DB::table('plans')->insert([
                'name' => '무료 커뮤니티 플랜',
                'slug' => 'free-community',
                'description' => '커뮤니티 플랜의 모든 기능을 무료로 사용할 수 있지만, 제한사항이 더 엄격합니다.',
                'type' => 'community',
                'price' => 0,
                'billing_type' => 'free',
                'one_time_price' => null,
                'features' => json_encode([
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
                ]),
                'limits' => json_encode([
                    'boards' => 5,        // 제한적 (유료 무제한에서 5개로)
                    'widgets' => 10,      // 제한적 (유료 무제한에서 10개로)
                    'custom_pages' => 5,  // 제한적 (유료 무제한에서 5개로)
                    'users' => 50,        // 제한적 (유료 무제한에서 50명으로)
                    'storage' => 2048,     // 제한적 (유료 20GB에서 2GB로)
                ]),
                'traffic_limit_mb' => 10240, // 제한적 (유료 20GB에서 10GB로)
                'sort_order' => 3,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plans')->whereIn('slug', ['free-landing', 'free-brand', 'free-community'])->delete();
    }
};

