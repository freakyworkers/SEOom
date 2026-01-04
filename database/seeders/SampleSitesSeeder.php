<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\MainWidgetContainer;
use App\Models\MainWidget;
use App\Models\Menu;
use App\Models\Map;
use Illuminate\Support\Facades\Hash;

class SampleSitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 샘플 사이트 생성을 시작합니다...');

        // 1. 랜딩페이지 샘플
        $this->createLandingSample();

        // 2. 회사 홈페이지 샘플
        $this->createBrandSample();

        // 3. 커뮤니티 샘플
        $this->createCommunitySample();

        $this->command->info('✅ 샘플 사이트 생성이 완료되었습니다!');
        $this->command->info('');
        $this->command->info('📌 샘플 사이트 접속 정보:');
        $this->command->info('   랜딩페이지: /site/sample-landing');
        $this->command->info('   회사홈페이지: /site/sample-brand');
        $this->command->info('   커뮤니티: /site/sample-community');
        $this->command->info('');
        $this->command->info('🔐 샘플 관리자 로그인: admin@sample.com / sample1234');
    }

    /**
     * 랜딩페이지 샘플 생성
     */
    private function createLandingSample(): void
    {
        $this->command->info('📄 랜딩페이지 샘플 생성 중...');

        // 기존 샘플 사이트 삭제
        $existingSite = Site::where('slug', 'sample-landing')->first();
        if ($existingSite) {
            $this->deleteSiteData($existingSite);
        }

        // 사이트 생성
        $site = Site::create([
            'name' => '샘플 랜딩페이지',
            'slug' => 'sample-landing',
            'plan' => 'landing-plan',
            'status' => 'active',
            'is_master_site' => false,
            'is_sample' => true,
            'storage_used_mb' => 0,
            'traffic_used_mb' => 0,
            'traffic_reset_date' => now()->addMonth()->startOfMonth(),
        ]);

        // 관리자 계정 생성
        $this->createSampleAdmin($site);

        // 기본 설정
        $this->createLandingSettings($site);

        // 기본 지도 생성
        $this->createDefaultMaps($site);

        // 메인 위젯 생성 - 랜딩페이지 스타일
        $this->createLandingWidgets($site);

        // 메뉴 생성
        $this->createLandingMenus($site);

        $this->command->info('   ✓ 랜딩페이지 샘플 완료');
    }

    /**
     * 회사 홈페이지 샘플 생성
     */
    private function createBrandSample(): void
    {
        $this->command->info('🏢 회사 홈페이지 샘플 생성 중...');

        // 기존 샘플 사이트 삭제
        $existingSite = Site::where('slug', 'sample-brand')->first();
        if ($existingSite) {
            $this->deleteSiteData($existingSite);
        }

        // 사이트 생성
        $site = Site::create([
            'name' => '샘플 회사홈페이지',
            'slug' => 'sample-brand',
            'plan' => 'brand-plan',
            'status' => 'active',
            'is_master_site' => false,
            'is_sample' => true,
            'storage_used_mb' => 0,
            'traffic_used_mb' => 0,
            'traffic_reset_date' => now()->addMonth()->startOfMonth(),
        ]);

        // 관리자 계정 생성
        $this->createSampleAdmin($site);

        // 기본 설정
        $this->createBrandSettings($site);

        // 기본 지도 생성
        $this->createDefaultMaps($site);

        // 메인 위젯 생성 - 회사 홈페이지 스타일
        $this->createBrandWidgets($site);

        // 게시판 생성
        $this->createBrandBoards($site);

        // 메뉴 생성
        $this->createBrandMenus($site);

        $this->command->info('   ✓ 회사 홈페이지 샘플 완료');
    }

    /**
     * 커뮤니티 샘플 생성
     */
    private function createCommunitySample(): void
    {
        $this->command->info('👥 커뮤니티 샘플 생성 중...');

        // 기존 샘플 사이트 삭제
        $existingSite = Site::where('slug', 'sample-community')->first();
        if ($existingSite) {
            $this->deleteSiteData($existingSite);
        }

        // 사이트 생성
        $site = Site::create([
            'name' => '샘플 커뮤니티',
            'slug' => 'sample-community',
            'plan' => 'community-plan',
            'status' => 'active',
            'is_master_site' => false,
            'is_sample' => true,
            'storage_used_mb' => 0,
            'traffic_used_mb' => 0,
            'traffic_reset_date' => now()->addMonth()->startOfMonth(),
        ]);

        // 관리자 계정 생성
        $this->createSampleAdmin($site);

        // 기본 설정
        $this->createCommunitySettings($site);

        // 기본 지도 생성
        $this->createDefaultMaps($site);

        // 메인 위젯 생성 - 커뮤니티 스타일
        $this->createCommunityWidgets($site);

        // 게시판 생성
        $this->createCommunityBoards($site);

        // 메뉴 생성
        $this->createCommunityMenus($site);

        $this->command->info('   ✓ 커뮤니티 샘플 완료');
    }

    /**
     * 샘플 관리자 계정 생성
     */
    private function createSampleAdmin(Site $site): void
    {
        User::create([
            'site_id' => $site->id,
            'name' => '샘플 관리자',
            'username' => 'admin',
            'nickname' => '관리자',
            'email' => 'admin@sample.com',
            'password' => Hash::make('sample1234'),
            'role' => 'admin',
            'points' => 1000,
        ]);
    }

    /**
     * 기존 사이트 데이터 삭제
     */
    private function deleteSiteData(Site $site): void
    {
        // 관련 데이터 삭제
        MainWidget::where('site_id', $site->id)->delete();
        MainWidgetContainer::where('site_id', $site->id)->delete();
        Menu::where('site_id', $site->id)->delete();
        Post::where('site_id', $site->id)->forceDelete();
        Board::where('site_id', $site->id)->forceDelete();
        User::where('site_id', $site->id)->forceDelete();
        SiteSetting::where('site_id', $site->id)->delete();
        Map::where('site_id', $site->id)->delete();
        $site->forceDelete();
    }

    /**
     * 기본 지도 생성
     */
    private function createDefaultMaps(Site $site): void
    {
        Map::create([
            'site_id' => $site->id,
            'name' => '본사 위치',
            'map_type' => 'kakao',
            'address' => '서울특별시 강남구 테헤란로 152',
            'latitude' => 37.5013,
            'longitude' => 127.0396,
            'zoom' => 15,
        ]);
    }

    // ========================================
    // 랜딩페이지 설정 및 위젯
    // ========================================

    private function createLandingSettings(Site $site): void
    {
        $settings = [
            'site_name' => '샘플 랜딩페이지',
            'site_description' => '세움빌더로 만든 랜딩페이지 샘플입니다.',
            'theme_sidebar' => 'none',
            'theme_top' => 'theme1',
            'theme_primary_color' => '#3B82F6',
            'theme_secondary_color' => '#1E40AF',
            'header_transparent' => true,
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::create([
                'site_id' => $site->id,
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }
    }

    private function createLandingWidgets(Site $site): void
    {
        // 히어로 섹션 (전체 화면)
        $container1 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 1,
            'vertical_align' => 'center',
            'full_width' => true,
            'full_height' => true,
            'widget_spacing' => 20,
            'background_type' => 'gradient',
            'background_gradient_start' => '#667eea',
            'background_gradient_end' => '#764ba2',
            'background_gradient_angle' => 135,
            'order' => 1,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container1->id,
            'column_index' => 0,
            'type' => 'block',
            'title' => '히어로 섹션',
            'settings' => [
                'title' => '비즈니스를 성장시키는<br>가장 스마트한 방법',
                'content' => '우리의 솔루션으로 비즈니스의 가능성을 확장하세요.<br>간편한 시작, 강력한 성과.',
                'button_text' => '무료로 시작하기',
                'button_url' => '/register',
                'button_style' => 'primary',
                'text_align' => 'center',
                'title_size' => '3rem',
                'content_size' => '1.25rem',
                'title_color' => '#ffffff',
                'content_color' => '#e0e0e0',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        // 특징 섹션
        $container2 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 3,
            'vertical_align' => 'top',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'color',
            'background_color' => '#ffffff',
            'order' => 2,
        ]);

        $features = [
            ['icon' => '🚀', 'title' => '빠른 시작', 'desc' => '복잡한 설정 없이 5분 만에 시작하세요.'],
            ['icon' => '🔒', 'title' => '안전한 보안', 'desc' => 'SSL 인증서와 강력한 보안을 기본 제공합니다.'],
            ['icon' => '📱', 'title' => '반응형 디자인', 'desc' => '모든 디바이스에서 완벽하게 작동합니다.'],
        ];

        foreach ($features as $index => $feature) {
            MainWidget::create([
                'site_id' => $site->id,
                'container_id' => $container2->id,
                'column_index' => $index,
                'type' => 'block',
                'title' => $feature['title'],
                'settings' => [
                    'title' => $feature['icon'] . ' ' . $feature['title'],
                    'content' => $feature['desc'],
                    'text_align' => 'center',
                    'title_size' => '1.5rem',
                    'content_size' => '1rem',
                    'padding' => '2rem',
                ],
                'order' => $index + 1,
                'is_active' => true,
            ]);
        }

        // CTA 섹션
        $container3 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 1,
            'vertical_align' => 'center',
            'full_width' => true,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'color',
            'background_color' => '#1E40AF',
            'order' => 3,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container3->id,
            'column_index' => 0,
            'type' => 'block',
            'title' => 'CTA',
            'settings' => [
                'title' => '지금 바로 시작하세요',
                'content' => '무료 체험으로 모든 기능을 경험해보세요.',
                'button_text' => '무료 체험 시작',
                'button_url' => '/register',
                'button_style' => 'light',
                'text_align' => 'center',
                'title_color' => '#ffffff',
                'content_color' => '#e0e0e0',
                'padding' => '4rem 2rem',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        // 컨택트 폼 섹션
        $container4 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 2,
            'vertical_align' => 'top',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 30,
            'background_type' => 'color',
            'background_color' => '#f8fafc',
            'order' => 4,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container4->id,
            'column_index' => 0,
            'type' => 'block',
            'title' => '문의 안내',
            'settings' => [
                'title' => '문의하기',
                'content' => '궁금한 점이 있으시면 언제든지 문의해주세요.<br><br>📧 contact@sample.com<br>📞 02-1234-5678<br>📍 서울시 강남구 테헤란로 152',
                'text_align' => 'left',
                'padding' => '2rem',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container4->id,
            'column_index' => 1,
            'type' => 'contact_form',
            'title' => '문의 폼',
            'settings' => [
                'show_title' => false,
            ],
            'order' => 1,
            'is_active' => true,
        ]);
    }

    private function createLandingMenus(Site $site): void
    {
        Menu::create(['site_id' => $site->id, 'title' => '홈', 'url' => '/', 'order' => 1, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '서비스', 'url' => '#services', 'order' => 2, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '문의', 'url' => '#contact', 'order' => 3, 'is_active' => true]);
    }

    // ========================================
    // 회사 홈페이지 설정 및 위젯
    // ========================================

    private function createBrandSettings(Site $site): void
    {
        $settings = [
            'site_name' => '샘플 회사홈페이지',
            'site_description' => '세움빌더로 만든 회사 홈페이지 샘플입니다.',
            'theme_sidebar' => 'none',
            'theme_top' => 'theme2',
            'theme_primary_color' => '#10B981',
            'theme_secondary_color' => '#059669',
            'header_transparent' => false,
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::create([
                'site_id' => $site->id,
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }
    }

    private function createBrandWidgets(Site $site): void
    {
        // 회사 소개 섹션
        $container1 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 2,
            'vertical_align' => 'center',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 30,
            'background_type' => 'color',
            'background_color' => '#ffffff',
            'order' => 1,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container1->id,
            'column_index' => 0,
            'type' => 'image',
            'title' => '회사 이미지',
            'settings' => [
                'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&h=400&fit=crop',
                'alt_text' => '회사 이미지',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container1->id,
            'column_index' => 1,
            'type' => 'block',
            'title' => '회사 소개',
            'settings' => [
                'title' => '회사 소개',
                'content' => '우리 회사는 2020년에 설립되어 혁신적인 솔루션을 제공하고 있습니다.<br><br>고객의 성공이 우리의 성공이라는 믿음으로, 최고의 서비스를 제공하기 위해 끊임없이 노력합니다.',
                'text_align' => 'left',
                'padding' => '2rem',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        // 서비스 섹션
        $container2 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 1,
            'vertical_align' => 'center',
            'full_width' => true,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'color',
            'background_color' => '#f0fdf4',
            'order' => 2,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container2->id,
            'column_index' => 0,
            'type' => 'block',
            'title' => '서비스 소개',
            'settings' => [
                'title' => '우리의 서비스',
                'content' => '고객의 니즈에 맞춘 다양한 서비스를 제공합니다.',
                'text_align' => 'center',
                'title_size' => '2rem',
                'padding' => '3rem 2rem 1rem',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        // 서비스 카드 섹션
        $container3 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 3,
            'vertical_align' => 'top',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'color',
            'background_color' => '#f0fdf4',
            'order' => 3,
        ]);

        $services = [
            ['title' => '웹 개발', 'desc' => '최신 기술을 활용한 웹사이트 및 웹 애플리케이션 개발'],
            ['title' => '모바일 앱', 'desc' => 'iOS, Android 네이티브 및 크로스플랫폼 앱 개발'],
            ['title' => '컨설팅', 'desc' => 'IT 전략 수립 및 디지털 전환 컨설팅 서비스'],
        ];

        foreach ($services as $index => $service) {
            MainWidget::create([
                'site_id' => $site->id,
                'container_id' => $container3->id,
                'column_index' => $index,
                'type' => 'block',
                'title' => $service['title'],
                'settings' => [
                    'title' => $service['title'],
                    'content' => $service['desc'],
                    'text_align' => 'center',
                    'padding' => '2rem',
                    'background_color' => '#ffffff',
                    'border_radius' => '0.5rem',
                    'box_shadow' => true,
                ],
                'order' => $index + 1,
                'is_active' => true,
            ]);
        }

        // 지도 섹션
        $container4 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 2,
            'vertical_align' => 'top',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 30,
            'background_type' => 'color',
            'background_color' => '#ffffff',
            'order' => 4,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container4->id,
            'column_index' => 0,
            'type' => 'block',
            'title' => '오시는 길',
            'settings' => [
                'title' => '오시는 길',
                'content' => '📍 서울시 강남구 테헤란로 152<br><br>🚇 지하철: 2호선 강남역 1번 출구<br>🚌 버스: 146, 341, 360<br>🚗 주차: 지하 주차장 이용 가능',
                'text_align' => 'left',
                'padding' => '2rem',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container4->id,
            'column_index' => 1,
            'type' => 'map',
            'title' => '지도',
            'settings' => [
                'map_id' => Map::where('site_id', $site->id)->first()?->id,
                'height' => '300px',
            ],
            'order' => 1,
            'is_active' => true,
        ]);
    }

    private function createBrandBoards(Site $site): void
    {
        Board::create([
            'site_id' => $site->id,
            'name' => '공지사항',
            'slug' => 'notice',
            'description' => '회사의 중요한 소식을 전해드립니다.',
            'type' => 'normal',
            'order' => 1,
            'is_active' => true,
        ]);

        Board::create([
            'site_id' => $site->id,
            'name' => '뉴스',
            'slug' => 'news',
            'description' => '업계 뉴스와 트렌드를 공유합니다.',
            'type' => 'blog',
            'order' => 2,
            'is_active' => true,
        ]);
    }

    private function createBrandMenus(Site $site): void
    {
        Menu::create(['site_id' => $site->id, 'title' => '홈', 'url' => '/', 'order' => 1, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '회사소개', 'url' => '#about', 'order' => 2, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '서비스', 'url' => '#services', 'order' => 3, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '공지사항', 'url' => '/boards/notice', 'order' => 4, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '오시는 길', 'url' => '#location', 'order' => 5, 'is_active' => true]);
    }

    // ========================================
    // 커뮤니티 설정 및 위젯
    // ========================================

    private function createCommunitySettings(Site $site): void
    {
        $settings = [
            'site_name' => '샘플 커뮤니티',
            'site_description' => '세움빌더로 만든 커뮤니티 샘플입니다.',
            'theme_sidebar' => 'right',
            'theme_top' => 'theme3',
            'theme_primary_color' => '#8B5CF6',
            'theme_secondary_color' => '#7C3AED',
            'header_transparent' => false,
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::create([
                'site_id' => $site->id,
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }
    }

    private function createCommunityWidgets(Site $site): void
    {
        // 환영 메시지
        $container1 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 1,
            'vertical_align' => 'center',
            'full_width' => true,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'gradient',
            'background_gradient_start' => '#8B5CF6',
            'background_gradient_end' => '#EC4899',
            'background_gradient_angle' => 135,
            'order' => 1,
        ]);

        MainWidget::create([
            'site_id' => $site->id,
            'container_id' => $container1->id,
            'column_index' => 0,
            'type' => 'block',
            'title' => '환영 메시지',
            'settings' => [
                'title' => '샘플 커뮤니티에 오신 것을 환영합니다!',
                'content' => '다양한 주제로 소통하고 정보를 공유하는 공간입니다.',
                'text_align' => 'center',
                'title_color' => '#ffffff',
                'content_color' => '#e0e0e0',
                'padding' => '3rem 2rem',
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        // 게시판 목록
        $container2 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 2,
            'vertical_align' => 'top',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'color',
            'background_color' => '#ffffff',
            'order' => 2,
        ]);

        // 공지사항 게시판
        $noticeBoard = Board::where('site_id', $site->id)->where('slug', 'notice')->first();
        if ($noticeBoard) {
            MainWidget::create([
                'site_id' => $site->id,
                'container_id' => $container2->id,
                'column_index' => 0,
                'type' => 'board',
                'title' => '공지사항',
                'settings' => [
                    'board_id' => $noticeBoard->id,
                    'show_title' => true,
                    'posts_count' => 5,
                ],
                'order' => 1,
                'is_active' => true,
            ]);
        }

        // 자유게시판
        $freeBoard = Board::where('site_id', $site->id)->where('slug', 'free')->first();
        if ($freeBoard) {
            MainWidget::create([
                'site_id' => $site->id,
                'container_id' => $container2->id,
                'column_index' => 1,
                'type' => 'board',
                'title' => '자유게시판',
                'settings' => [
                    'board_id' => $freeBoard->id,
                    'show_title' => true,
                    'posts_count' => 5,
                ],
                'order' => 1,
                'is_active' => true,
            ]);
        }

        // 갤러리
        $container3 = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => 1,
            'vertical_align' => 'top',
            'full_width' => false,
            'full_height' => false,
            'widget_spacing' => 20,
            'background_type' => 'color',
            'background_color' => '#f8fafc',
            'order' => 3,
        ]);

        $galleryBoard = Board::where('site_id', $site->id)->where('slug', 'gallery')->first();
        if ($galleryBoard) {
            MainWidget::create([
                'site_id' => $site->id,
                'container_id' => $container3->id,
                'column_index' => 0,
                'type' => 'gallery',
                'title' => '갤러리',
                'settings' => [
                    'board_id' => $galleryBoard->id,
                    'show_title' => true,
                    'columns' => 4,
                    'posts_count' => 8,
                ],
                'order' => 1,
                'is_active' => true,
            ]);
        }
    }

    private function createCommunityBoards(Site $site): void
    {
        $admin = User::where('site_id', $site->id)->where('role', 'admin')->first();

        // 공지사항
        $notice = Board::create([
            'site_id' => $site->id,
            'name' => '공지사항',
            'slug' => 'notice',
            'description' => '커뮤니티 공지사항입니다.',
            'type' => 'normal',
            'order' => 1,
            'is_active' => true,
        ]);

        // 샘플 공지 게시글
        Post::create([
            'site_id' => $site->id,
            'board_id' => $notice->id,
            'user_id' => $admin?->id,
            'title' => '샘플 커뮤니티에 오신 것을 환영합니다!',
            'content' => '<p>안녕하세요! 샘플 커뮤니티입니다.</p><p>이 커뮤니티는 세움빌더로 만들어진 샘플 사이트입니다.</p><p>다양한 기능을 체험해보세요!</p>',
            'views' => 100,
        ]);

        // 자유게시판
        $free = Board::create([
            'site_id' => $site->id,
            'name' => '자유게시판',
            'slug' => 'free',
            'description' => '자유롭게 글을 작성해보세요.',
            'type' => 'normal',
            'order' => 2,
            'is_active' => true,
        ]);

        // 샘플 게시글
        Post::create([
            'site_id' => $site->id,
            'board_id' => $free->id,
            'user_id' => $admin?->id,
            'title' => '첫 번째 게시글입니다',
            'content' => '<p>자유게시판의 첫 번째 게시글입니다.</p><p>자유롭게 의견을 나눠보세요!</p>',
            'views' => 50,
        ]);

        // Q&A
        Board::create([
            'site_id' => $site->id,
            'name' => '질문과 답변',
            'slug' => 'qna',
            'description' => '궁금한 점을 질문해주세요.',
            'type' => 'qa',
            'order' => 3,
            'is_active' => true,
        ]);

        // 갤러리
        Board::create([
            'site_id' => $site->id,
            'name' => '갤러리',
            'slug' => 'gallery',
            'description' => '사진을 공유하는 공간입니다.',
            'type' => 'photo',
            'order' => 4,
            'is_active' => true,
        ]);
    }

    private function createCommunityMenus(Site $site): void
    {
        Menu::create(['site_id' => $site->id, 'title' => '홈', 'url' => '/', 'order' => 1, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '공지사항', 'url' => '/boards/notice', 'order' => 2, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '자유게시판', 'url' => '/boards/free', 'order' => 3, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => 'Q&A', 'url' => '/boards/qna', 'order' => 4, 'is_active' => true]);
        Menu::create(['site_id' => $site->id, 'title' => '갤러리', 'url' => '/boards/gallery', 'order' => 5, 'is_active' => true]);
    }
}

