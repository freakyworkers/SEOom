<?php

namespace App\Services;

use App\Models\Site;
use App\Models\User;
use App\Models\Board;
use App\Models\SiteSetting;
use App\Models\Map;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiteProvisionService
{
    /**
     * Create a new site with default data.
     * 
     * @param array $data Site data
     * @param bool $isMasterSite Whether this is a master site (no admin user creation)
     */
    public function provision(array $data, bool $isMasterSite = false): Site
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Site::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // 플랜 정보 가져오기
        $planSlug = $data['plan'] ?? 'free';
        $plan = Plan::where('slug', $planSlug)->first();
        
        // 플랜의 limits를 사용 (서버 용량 플랜 구독 시에만 직접 설정됨)
        // 일반 플랜의 경우 null로 설정하여 플랜의 limits를 항상 사용하도록 함
        $site = Site::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'domain' => $data['domain'] ?? null,
            'plan' => $planSlug,
            'status' => $data['status'] ?? 'active',
            'is_master_site' => $data['is_master_site'] ?? false,
            'created_by' => $data['created_by'] ?? null,
            'storage_limit_mb' => null, // 플랜의 limits 사용
            'traffic_limit_mb' => null, // 플랜의 traffic_limit_mb 사용
            'storage_used_mb' => 0,
            'traffic_used_mb' => 0,
            'traffic_reset_date' => now()->addMonth()->startOfMonth(),
        ]);

        // 마스터 사이트가 아닌 경우에만 관리자 계정 생성
        if (!$isMasterSite && isset($data['admin_email']) && isset($data['admin_password'])) {
            // username 중복 체크 (해당 사이트 내에서)
            if (isset($data['admin_username'])) {
                $existingUser = User::where('site_id', $site->id)
                    ->where('username', $data['admin_username'])
                    ->first();
                
                if ($existingUser) {
                    throw new \Exception('이미 사용 중인 아이디입니다. 다른 아이디를 선택해주세요.');
                }
            }

            $admin = User::create([
                'site_id' => $site->id,
                'name' => $data['admin_name'] ?? 'Administrator',
                'username' => $data['admin_username'] ?? null,
                'nickname' => null,
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role' => 'admin',
            ]);
        }

        // 로그인 방식 설정 저장
        if (!$isMasterSite && isset($data['login_method'])) {
            $site->setSetting('registration_login_method', $data['login_method']);
        }

        // 기본 게시판 생성 기능 제거됨
        // $this->createDefaultBoards($site);

        // Create default settings
        $this->createDefaultSettings($site);

        // Create default maps
        $this->createDefaultMaps($site);

        // 무료 플랜이 아닌 경우에만 구독 생성 (플랜 구매 후)
        // 사이트 생성 시에는 구독을 생성하지 않음
        // 플랜 구매는 별도로 처리됨

        return $site;
    }

    /**
     * Create default boards for the site.
     */
    protected function createDefaultBoards(Site $site): void
    {
        $defaultBoards = [
            [
                'name' => '공지사항',
                'slug' => 'notice',
                'description' => '중요한 공지사항을 확인하세요.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => '자유게시판',
                'slug' => 'free',
                'description' => '자유롭게 글을 작성하세요.',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => '질문과 답변',
                'slug' => 'qna',
                'description' => '궁금한 점을 질문하세요.',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($defaultBoards as $boardData) {
            Board::create(array_merge($boardData, [
                'site_id' => $site->id,
            ]));
        }
    }

    /**
     * Create default settings for the site.
     */
    protected function createDefaultSettings(Site $site): void
    {
        // 사이드 위젯 기능이 없는 플랜의 경우 사이드바를 'none'으로 설정
        $themeSidebar = $site->hasFeature('sidebar_widgets') ? 'left' : 'none';
        
        $defaultSettings = [
            'site_name' => $site->name,
            'site_description' => $site->name . ' 사이트입니다.',
            'site_keywords' => '',
            'site_logo' => '',
            'site_favicon' => '',
            'theme_sidebar' => $themeSidebar,
        ];

        foreach ($defaultSettings as $key => $value) {
            SiteSetting::create([
                'site_id' => $site->id,
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /**
     * Create default maps for the site.
     */
    protected function createDefaultMaps(Site $site): void
    {
        $defaultMaps = [
            [
                'name' => '구글 지도',
                'map_type' => 'google',
                'address' => '서울특별시 강남구 테헤란로 152',
                'latitude' => 37.5013,
                'longitude' => 127.0396,
                'zoom' => 15,
            ],
            [
                'name' => '카카오맵',
                'map_type' => 'kakao',
                'address' => '서울특별시 강남구 테헤란로 152',
                'latitude' => 37.5013,
                'longitude' => 127.0396,
                'zoom' => 15,
            ],
            [
                'name' => '네이버 지도',
                'map_type' => 'naver',
                'address' => '서울특별시 강남구 테헤란로 152',
                'latitude' => 37.5013,
                'longitude' => 127.0396,
                'zoom' => 15,
            ],
        ];

        foreach ($defaultMaps as $mapData) {
            Map::create(array_merge($mapData, [
                'site_id' => $site->id,
            ]));
        }
    }

    /**
     * Delete a site and all related data.
     */
    public function deprovision(Site $site): bool
    {
        // Soft delete all related data
        $site->users()->delete();
        $site->boards()->delete();
        $site->posts()->delete();
        $site->comments()->delete();
        $site->settings()->delete();
        $site->maps()->delete();

        // Delete the site
        return $site->delete();
    }
}


