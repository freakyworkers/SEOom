<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => '랜딩페이지 플랜',
                'slug' => 'landing',
                'description' => '간단한 랜딩페이지 제작에 최적화된 플랜입니다.',
                'type' => 'landing',
                'price' => 0,
                'features' => [
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
                    'sidebar_widget_types' => [], // No sidebar widgets for landing page
                    'main_widget_types' => [
                        'image', 'custom_html', 'block', 'block_slide',
                    ],
                    'custom_page_widget_types' => [
                        'image', 'custom_html', 'block', 'block_slide',
                    ],
                ],
                'limits' => [
                    'boards' => 3,
                    'widgets' => 5,
                    'custom_pages' => 5,
                    'users' => 100,
                    'storage' => 1024, // MB
                ],
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => '브랜드 플랜',
                'slug' => 'brand',
                'description' => '회사, 병원 등 브랜드 홈페이지 제작에 적합한 플랜입니다.',
                'type' => 'brand',
                'price' => 29000,
                'features' => [
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
                ],
                'limits' => [
                    'boards' => 10,
                    'widgets' => 20,
                    'custom_pages' => 20,
                    'users' => 1000,
                    'storage' => 5120, // MB
                ],
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => '커뮤니티 플랜',
                'slug' => 'community',
                'description' => '커뮤니티 홈페이지 제작에 최적화된 플랜입니다. 모든 기능을 무제한으로 사용할 수 있습니다.',
                'type' => 'community',
                'price' => 59000,
                'features' => [
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
                ],
                'limits' => [
                    'boards' => null, // 무제한
                    'widgets' => null,
                    'custom_pages' => null,
                    'users' => null,
                    'storage' => 20480, // MB
                ],
                'sort_order' => 3,
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('Plans created successfully!');
    }
}
