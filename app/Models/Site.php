<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'status',
        'is_master_site',
        'is_sample',
        'login_type',
        'test_admin',
        'created_by',
        'storage_used_mb',
        'storage_limit_mb',
        'traffic_used_mb',
        'traffic_limit_mb',
        'traffic_reset_date',
        'cloudflare_zone_id',
        'nameservers',
    ];

    protected $casts = [
        'status' => 'string',
        'is_master_site' => 'boolean',
        'is_sample' => 'boolean',
        'nameservers' => 'array',
        'traffic_reset_date' => 'date',
        'test_admin' => 'array',
    ];

    /**
     * Check if this site is a sample site.
     */
    public function isSample(): bool
    {
        return $this->is_sample === true;
    }

    /**
     * Get the users for the site.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the boards for the site.
     */
    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    /**
     * Get the posts for the site.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments for the site.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the settings for the site.
     */
    public function settings()
    {
        return $this->hasMany(SiteSetting::class);
    }

    /**
     * Get the maps for the site.
     */
    public function maps()
    {
        return $this->hasMany(Map::class);
    }

    /**
     * Get the subscription for the site.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get the user who created this site.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get a setting value by key.
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public function setSetting($key, $value)
    {
        // SiteSetting의 setValueAttribute가 자동으로 인코딩하므로 배열을 그대로 전달
        $setting = $this->settings()->where('key', $key)->first();
        if ($setting) {
            // 기존 레코드 업데이트 - setValueAttribute를 사용하기 위해 직접 할당
            $setting->value = $value; // 배열을 직접 할당하면 setValueAttribute가 자동으로 인코딩
            $setting->save();
            return $setting;
        } else {
            // 새 레코드 생성
            return $this->settings()->create([
                'key' => $key,
                'value' => $value, // 배열을 직접 할당하면 setValueAttribute가 자동으로 인코딩
            ]);
        }
    }

    /**
     * Check if this site is a master site.
     */
    public function isMasterSite(): bool
    {
        return $this->is_master_site === true;
    }
    
    /**
     * Check if current request is using custom domain or subdomain (not slug-based routing).
     */
    public function isUsingDirectDomain(): bool
    {
        if (!$this->slug) {
            return false;
        }
        
        $currentHost = request()->getHost();
        $masterDomain = config('app.master_domain', 'seoomweb.com');
        
        // 커스텀 도메인 체크
        if ($this->domain && ($currentHost === $this->domain || $currentHost === 'www.' . $this->domain)) {
            return true;
        }
        
        // 서브도메인 체크 (예: adfreak1.seoomweb.com)
        $subdomainPattern = $this->slug . '.' . $masterDomain;
        if ($currentHost === $subdomainPattern || $currentHost === 'www.' . $subdomainPattern) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get home URL for this site (custom domain/subdomain or slug-based).
     */
    public function getHomeUrl(): string
    {
        if ($this->isUsingDirectDomain()) {
            return '/';
        }
        
        if ($this->isMasterSite()) {
            return '/';
        }
        
        return route('home', ['site' => $this->slug]);
    }
    
    /**
     * Get admin dashboard URL for this site (custom domain/subdomain or slug-based).
     */
    public function getAdminDashboardUrl(): string
    {
        if ($this->isMasterSite()) {
            // 마스터 사이트의 경우 라우트가 존재하면 사용, 아니면 직접 경로 사용
            if (\Route::has('master.admin.dashboard')) {
                return route('master.admin.dashboard');
            }
            return '/admin/dashboard';
        }
        
        if ($this->isUsingDirectDomain()) {
            return '/admin/dashboard';
        }
        
        return route('admin.dashboard', ['site' => $this->slug]);
    }
    
    /**
     * Get admin route URL for this site (custom domain/subdomain or slug-based).
     */
    public function getAdminRouteUrl(string $routeName, array $parameters = []): string
    {
        if ($this->isMasterSite()) {
            $masterRouteName = 'master.' . $routeName;
            if (Route::has($masterRouteName)) {
                return route($masterRouteName, $parameters);
            }
        }
        
        if ($this->isUsingDirectDomain()) {
            // 커스텀 도메인/서브도메인인 경우 /admin/{route} 형태로 변환
            // routeName 예: 'admin.dashboard', 'admin.users.detail'
            $routePath = str_replace('admin.', '', $routeName);
            $routePath = str_replace('.', '/', $routePath);
            $url = '/admin/' . $routePath;
            
            // 파라미터가 있으면 URL 경로에 포함 (site 파라미터 제외)
            // 예: admin.users.detail + ['user' => 1] => /admin/users/1
            $pathParams = [];
            $queryParams = [];
            
            foreach ($parameters as $key => $value) {
                if ($key === 'site') {
                    continue; // site 파라미터는 제외
                }
                
                // URL 경로에 포함할 파라미터인지 확인 (일반적으로 모델 ID 등)
                // 쿼리 스트링으로 보낼 파라미터인지 확인
                if (is_numeric($value) || is_string($value)) {
                    // 라우트 경로에 포함 (예: /admin/users/{user})
                    $pathParams[] = $value;
                } else {
                    $queryParams[$key] = $value;
                }
            }
            
            // 경로 파라미터가 있으면 URL에 추가
            if (!empty($pathParams)) {
                $url .= '/' . implode('/', $pathParams);
            }
            
            // 쿼리 파라미터가 있으면 쿼리 스트링으로 추가
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }
            
            return $url;
        }
        
        return route($routeName, array_merge(['site' => $this->slug], $parameters));
    }

    /**
     * Check if site can use all features (master site or premium plan).
     */
    public function canUseAllFeatures(): bool
    {
        return $this->isMasterSite() || $this->plan === 'premium';
    }

    /**
     * Get the master site (root domain site).
     */
    public static function getMasterSite(): ?self
    {
        try {
            // Check if table exists before querying
            if (!Schema::hasTable('sites')) {
                return null;
            }
            return static::where('is_master_site', true)
                ->where('status', 'active')
                ->first();
        } catch (\Exception $e) {
            // If table doesn't exist or any other error, return null
            return null;
        }
    }

    /**
     * Get the plan model for this site.
     * 우선적으로 subscription의 plan을 반환하고, 없으면 site의 plan slug로 찾습니다.
     */
    public function planModel()
    {
        // subscription이 로드되지 않았으면 로드
        if (!$this->relationLoaded('subscription')) {
            $this->load('subscription.plan');
        }
        
        // subscription이 있고 plan이 있으면 subscription의 plan을 반환
        if ($this->subscription && $this->subscription->plan) {
            return $this->subscription->plan;
        }
        
        // 없으면 site의 plan slug로 찾기
        return Plan::where('slug', $this->plan)->first();
    }

    /**
     * Check if site has a specific feature.
     */
    public function hasFeature(string $featureKey): bool
    {
        // 마스터 사이트는 모든 기능 사용 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 사이트별 커스텀 features 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['main_features'])) {
                // 커스텀 features가 있으면 그것을 사용
                return in_array($featureKey, $customFeaturesArray['main_features'] ?? []);
            }
        }

        // 플랜의 features 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return false;
        }

        // 구조화된 features 확인
        return $planModel->hasMainFeature($featureKey);
    }

    /**
     * Get limit value for a specific resource.
     */
    public function getLimit(string $limitKey, $default = null)
    {
        // 마스터 사이트는 제한 없음
        if ($this->isMasterSite()) {
            return null; // null means unlimited
        }

        // 사이트별 커스텀 limits 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['limits'])) {
                $customLimits = $customFeaturesArray['limits'];
                if (isset($customLimits[$limitKey])) {
                    return $customLimits[$limitKey]; // null이면 무제한
                }
            }
        }

        // 플랜의 limits 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return $default;
        }

        $limits = $planModel->limits ?? [];
        return $limits[$limitKey] ?? $default;
    }

    /**
     * Check if site has a specific board type.
     */
    public function hasBoardType(string $boardType): bool
    {
        // 마스터 사이트는 모든 게시판 타입 사용 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 사이트별 커스텀 features 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['board_types'])) {
                return in_array($boardType, $customFeaturesArray['board_types'] ?? []);
            }
        }

        // 플랜의 board types 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return false;
        }

        return $planModel->hasBoardType($boardType);
    }

    /**
     * Check if site has a specific registration feature.
     */
    public function hasRegistrationFeature(string $feature): bool
    {
        // 마스터 사이트는 모든 회원가입 기능 사용 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 사이트별 커스텀 features 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['registration_features'])) {
                return in_array($feature, $customFeaturesArray['registration_features'] ?? []);
            }
        }

        // 플랜의 registration features 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return false;
        }

        return $planModel->hasRegistrationFeature($feature);
    }

    /**
     * Check if site can change domain.
     */
    public function canChangeDomain(): bool
    {
        // 마스터 사이트는 도메인 변경 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 무료 플랜은 도메인 변경 불가
        $planModel = $this->planModel();
        if ($planModel && $planModel->billing_type === 'free') {
            return false;
        }

        return true;
    }

    /**
     * Check if site has a specific sidebar widget type.
     */
    public function hasSidebarWidgetType(string $widgetType): bool
    {
        // 마스터 사이트는 모든 위젯 타입 사용 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 사이트별 커스텀 features 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['sidebar_widget_types'])) {
                return in_array($widgetType, $customFeaturesArray['sidebar_widget_types'] ?? []);
            }
        }

        // 플랜의 sidebar widget types 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return false;
        }

        $sidebarWidgetTypes = $planModel->sidebar_widget_types ?? [];
        return in_array($widgetType, $sidebarWidgetTypes);
    }

    /**
     * Check if site has a specific main widget type.
     */
public function hasMainWidgetType(string $widgetType): bool
    {
        // create_site 위젯은 마스터 사이트에서만 사용 가능
        if ($widgetType === 'create_site') {
            return $this->isMasterSite();
        }
        
        // 마스터 사이트는 모든 위젯 타입 사용 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 사이트별 커스텀 features 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['main_widget_types'])) {
                return in_array($widgetType, $customFeaturesArray['main_widget_types'] ?? []);
            }
        }

        // 플랜의 main widget types 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return false;
        }

        $mainWidgetTypes = $planModel->main_widget_types ?? [];
        return in_array($widgetType, $mainWidgetTypes);
    }

    /**
     * Check if site has a specific custom page widget type.
     */
    public function hasCustomPageWidgetType(string $widgetType): bool
    {
        // create_site 위젯은 마스터 사이트에서만 사용 가능
        if ($widgetType === 'create_site') {
            return $this->isMasterSite();
        }
        
        // 마스터 사이트는 모든 위젯 타입 사용 가능
        if ($this->isMasterSite()) {
            return true;
        }

        // 사이트별 커스텀 features 확인 (우선순위)
        $customFeatures = $this->getSetting('custom_features', null);
        if ($customFeatures !== null) {
            $customFeaturesArray = is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true);
            if (is_array($customFeaturesArray) && isset($customFeaturesArray['custom_page_widget_types'])) {
                return in_array($widgetType, $customFeaturesArray['custom_page_widget_types'] ?? []);
            }
        }

        // 플랜의 custom page widget types 확인
        $planModel = $this->planModel();
        if (!$planModel) {
            return false;
        }

        $customPageWidgetTypes = $planModel->custom_page_widget_types ?? [];
        return in_array($widgetType, $customPageWidgetTypes);
    }

    /**
     * Get total storage limit including addons (MB).
     */
    public function getTotalStorageLimit(): ?int
    {
        // 플랜에서 스토리지 제한 가져오기
        $plan = $this->planModel();
        $planLimit = $plan && isset($plan->limits['storage']) ? (int)$plan->limits['storage'] : null;
        
        // 사이트에 직접 설정된 저장 용량이 있으면 사용 (서버 용량 플랜 구독 시)
        // 없으면 플랜의 limits 사용
        $baseLimit = $this->storage_limit_mb ?? $planLimit ?? 0;
        
        // 추가 구매한 저장 용량 합산
        $addonStorage = 0;
        try {
            $addonStorage = \App\Models\UserAddon::where('site_id', $this->id)
                ->where('status', 'active')
                ->whereHas('addonProduct', function($query) {
                    $query->where('type', 'storage');
                })
                ->sum('amount_mb') ?? 0;
        } catch (\Exception $e) {
            // 테이블이 없을 경우 0으로 처리
            $addonStorage = 0;
        }
        
        $total = (int) ($baseLimit + $addonStorage);
        
        // 플랜 제한이 null이고 추가 구매도 없으면 null 반환 (무제한)
        if ($planLimit === null && $addonStorage === 0 && $this->storage_limit_mb === null) {
            return null;
        }
        
        return $total > 0 ? $total : null;
    }

    /**
     * Get total traffic limit including addons (MB).
     */
    public function getTotalTrafficLimit(): ?int
    {
        // 플랜에서 트래픽 제한 가져오기
        $plan = $this->planModel();
        $planLimit = $plan ? $plan->traffic_limit_mb : null;
        
        // 사이트에 직접 설정된 트래픽이 있으면 사용 (서버 용량 플랜 구독 시)
        // 없으면 플랜의 traffic_limit_mb 사용
        $baseLimit = $this->traffic_limit_mb ?? $planLimit ?? 0;
        
        // 추가 구매한 트래픽 합산
        $addonTraffic = 0;
        try {
            $addonTraffic = \App\Models\UserAddon::where('site_id', $this->id)
                ->where('status', 'active')
                ->whereHas('addonProduct', function($query) {
                    $query->where('type', 'traffic');
                })
                ->sum('amount_mb') ?? 0;
        } catch (\Exception $e) {
            // 테이블이 없을 경우 0으로 처리
            $addonTraffic = 0;
        }
        
        $total = (int) ($baseLimit + $addonTraffic);
        
        // 플랜 제한이 null이고 추가 구매도 없으면 null 반환 (무제한)
        if ($planLimit === null && $addonTraffic === 0 && $this->traffic_limit_mb === null) {
            return null;
        }
        
        return $total > 0 ? $total : null;
    }

    /**
     * Get storage usage percentage.
     */
    public function getStorageUsagePercentage(): float
    {
        $limit = $this->getTotalStorageLimit();
        if ($limit <= 0) {
            return 0;
        }
        return min(100, ($this->storage_used_mb / $limit) * 100);
    }

    /**
     * Get traffic usage percentage.
     */
    public function getTrafficUsagePercentage(): float
    {
        $limit = $this->getTotalTrafficLimit();
        if ($limit <= 0) {
            return 0;
        }
        return min(100, ($this->traffic_used_mb / $limit) * 100);
    }

    /**
     * Get active addons for this site.
     */
    public function addons()
    {
        return $this->hasMany(\App\Models\UserAddon::class);
    }

    /**
     * Get board limit from plan.
     * Returns null for unlimited.
     */
    public function getBoardLimit(): ?int
    {
        $plan = $this->planModel();
        if (!$plan) {
            return 2; // 기본값
        }
        
        $limits = $plan->limits ?? [];
        return $limits['boards'] ?? null;
    }

    /**
     * Get widget limit from plan.
     * Returns null for unlimited.
     */
    public function getWidgetLimit(): ?int
    {
        $plan = $this->planModel();
        if (!$plan) {
            return 3; // 기본값
        }
        
        $limits = $plan->limits ?? [];
        return $limits['widgets'] ?? null;
    }

    /**
     * Get custom page limit from plan.
     * Returns null for unlimited.
     */
    public function getCustomPageLimit(): ?int
    {
        $plan = $this->planModel();
        if (!$plan) {
            return 2; // 기본값
        }
        
        $limits = $plan->limits ?? [];
        return $limits['custom_pages'] ?? null;
    }

    /**
     * Get user limit from plan.
     * Returns null for unlimited.
     */
    public function getUserLimit(): ?int
    {
        $plan = $this->planModel();
        if (!$plan) {
            return 20; // 기본값
        }
        
        $limits = $plan->limits ?? [];
        return $limits['users'] ?? null;
    }

    /**
     * Check if can create more boards.
     */
    public function canCreateBoard(): bool
    {
        $limit = $this->getBoardLimit();
        if ($limit === null) {
            return true; // 무제한
        }
        
        $currentCount = $this->boards()->count();
        return $currentCount < $limit;
    }

    /**
     * Check if can create more widgets.
     */
    public function canCreateWidget(): bool
    {
        $limit = $this->getWidgetLimit();
        if ($limit === null) {
            return true; // 무제한
        }
        
        // 모든 위젯 카운트 (사이드바 + 메인 + 커스텀페이지)
        $sidebarCount = \App\Models\SidebarWidget::where('site_id', $this->id)->count();
        $mainCount = \App\Models\MainWidget::where('site_id', $this->id)->count();
        $customPageWidgetCount = \App\Models\CustomPageWidget::where('site_id', $this->id)->count();
        
        $currentCount = $sidebarCount + $mainCount + $customPageWidgetCount;
        return $currentCount < $limit;
    }

    /**
     * Check if can create more custom pages.
     */
    public function canCreateCustomPage(): bool
    {
        $limit = $this->getCustomPageLimit();
        if ($limit === null) {
            return true; // 무제한
        }
        
        $currentCount = \App\Models\CustomPage::where('site_id', $this->id)->count();
        return $currentCount < $limit;
    }

    /**
     * Get remaining board count.
     * Returns null for unlimited.
     */
    public function getRemainingBoardCount(): ?int
    {
        $limit = $this->getBoardLimit();
        if ($limit === null) {
            return null; // 무제한
        }
        
        $currentCount = $this->boards()->count();
        return max(0, $limit - $currentCount);
    }

    /**
     * Get remaining widget count.
     * Returns null for unlimited.
     */
    public function getRemainingWidgetCount(): ?int
    {
        $limit = $this->getWidgetLimit();
        if ($limit === null) {
            return null; // 무제한
        }
        
        $sidebarCount = \App\Models\SidebarWidget::where('site_id', $this->id)->count();
        $mainCount = \App\Models\MainWidget::where('site_id', $this->id)->count();
        $customPageWidgetCount = \App\Models\CustomPageWidget::where('site_id', $this->id)->count();
        
        $currentCount = $sidebarCount + $mainCount + $customPageWidgetCount;
        return max(0, $limit - $currentCount);
    }

    /**
     * Get remaining custom page count.
     * Returns null for unlimited.
     */
    public function getRemainingCustomPageCount(): ?int
    {
        $limit = $this->getCustomPageLimit();
        if ($limit === null) {
            return null; // 무제한
        }
        
        $currentCount = \App\Models\CustomPage::where('site_id', $this->id)->count();
        return max(0, $limit - $currentCount);
    }

    /**
     * Get current usage stats for limits.
     */
    public function getLimitStats(): array
    {
        return [
            'boards' => [
                'current' => $this->boards()->count(),
                'limit' => $this->getBoardLimit(),
                'can_create' => $this->canCreateBoard(),
            ],
            'widgets' => [
                'current' => \App\Models\SidebarWidget::where('site_id', $this->id)->count() 
                           + \App\Models\MainWidget::where('site_id', $this->id)->count()
                           + \App\Models\CustomPageWidget::where('site_id', $this->id)->count(),
                'limit' => $this->getWidgetLimit(),
                'can_create' => $this->canCreateWidget(),
            ],
            'custom_pages' => [
                'current' => \App\Models\CustomPage::where('site_id', $this->id)->count(),
                'limit' => $this->getCustomPageLimit(),
                'can_create' => $this->canCreateCustomPage(),
            ],
        ];
    }
}


