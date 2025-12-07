<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\AddonProduct;
use App\Models\UserAddon;
use App\Models\User;
use App\Services\CloudflareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserMySitesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the user's sites.
     */
    public function index(Request $request, Site $site = null)
    {
        // 도메인 기반 접근 시 미들웨어에서 설정한 site 사용
        if (!$site) {
            $site = $request->attributes->get('site');
        }
        
        // 마스터 사이트인지 확인
        if (!$site || !$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        // 사용자가 만든 사이트 목록 가져오기
        $userSites = Site::where('created_by', $user->id)
            ->where('is_master_site', false)
            ->with(['subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 각 사이트의 구독 정보 및 서버 구독 정보 가져오기
        foreach ($userSites as $userSite) {
            // 구독 정보가 없으면 subscription 관계를 통해 가져오기
            if (!$userSite->subscription) {
                // user_id로 구독 찾기
                $subscription = Subscription::where('site_id', $userSite->id)
                    ->where('user_id', $user->id)
                    ->with('plan')
                    ->first();
                $userSite->subscription = $subscription;
            }
            
            // 서버 용량 구독 정보 가져오기
            $serverSubscription = Subscription::where('site_id', $userSite->id)
                ->where('user_id', $user->id)
                ->whereHas('plan', function($query) {
                    $query->where('type', 'server');
                })
                ->with('plan')
                ->first();
            
            // 실제 저장용량 계산 및 업데이트
            $storageUsedMB = $this->calculateStorageUsage($userSite);
            if ($userSite->storage_used_mb != $storageUsedMB) {
                $userSite->update(['storage_used_mb' => $storageUsedMB]);
                $userSite->refresh();
            }
            
            // 업데이트 후 관계 설정
            $userSite->serverSubscription = $serverSubscription;
        }

        return view('user-sites.index', compact('site', 'userSites'));
    }

    /**
     * Show plan selection page for changing plan.
     */
    public function showChangePlan(Site $site, Site $userSite = null)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        // 사용자가 만든 사이트 목록 가져오기 (구독이 있는 사이트만)
        $userSites = Site::where('created_by', $user->id)
            ->where('is_master_site', false)
            ->with(['subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($site) use ($user) {
                // 구독이 있는 사이트만 필터링
                if (!$site->subscription) {
                    $subscription = Subscription::where('site_id', $site->id)
                        ->where('user_id', $user->id)
                        ->with('plan')
                        ->first();
                    $site->subscription = $subscription;
                }
                return $site->subscription !== null;
            });

        // 특정 사이트가 지정된 경우
        if ($userSite) {
            // 사용자가 소유한 사이트인지 확인
            if ($userSite->created_by !== $user->id) {
                abort(403, '이 사이트를 변경할 권한이 없습니다.');
            }
        }

        // 모든 활성 플랜 가져오기 (서버 용량 플랜 제외 - 서버 업그레이드에서 별도 처리)
        $freePlans = Plan::where('is_active', true)
            ->where('billing_type', 'free')
            ->where('type', '!=', 'server')
            ->orderBy('sort_order')
            ->get();

        $paidPlans = Plan::where('is_active', true)
            ->whereIn('billing_type', ['one_time', 'monthly'])
            ->where('type', '!=', 'server')
            ->orderBy('sort_order')
            ->get();

        return view('user-sites.select-plan-change', compact('site', 'userSite', 'userSites', 'freePlans', 'paidPlans'));
    }

    /**
     * Process plan change.
     */
    public function changePlan(Request $request, Site $site, Site $userSite = null)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'target_site_id' => 'required|exists:sites,id',
            'immediate' => 'boolean',
        ]);

        // target_site_id로 사이트 가져오기
        $targetSite = Site::findOrFail($request->target_site_id);

        // 사용자가 소유한 사이트인지 확인
        if ($targetSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        // userSite가 null이면 targetSite로 설정
        if (!$userSite) {
            $userSite = $targetSite;
        }

        // 구독 정보 가져오기
        $subscription = Subscription::where('site_id', $userSite->id)
            ->where('user_id', $user->id)
            ->with('plan')
            ->first();

        if (!$subscription) {
            return redirect()->route('users.my-sites', ['site' => $site->slug])
                ->with('error', '구독 정보를 찾을 수 없습니다.');
        }

        $newPlan = Plan::findOrFail($request->plan_id);
        $immediate = true; // 항상 즉시 변경

        // 서버 용량 플랜으로 변경하는 경우는 별도 처리
        if ($newPlan->type === 'server') {
            // 서버 용량 플랜은 새로운 구독으로 생성해야 하므로, 기존 플랜 변경 로직과 다름
            // 현재는 기존 구독의 플랜을 변경하되, 결제 후 사이트 선택 페이지로 이동
            // 차액 계산
            $currentPlan = $subscription->plan;
            $currentPrice = (float) $currentPlan->price;
            $newPrice = (float) $newPlan->price;
            $priceDifference = $newPrice - $currentPrice;

            // 즉시 변경인 경우
            if ($immediate) {
                // 남은 기간 계산 (일 단위)
                $now = \Carbon\Carbon::now();
                $periodEnd = $subscription->current_period_end ?? $now;
                
                if ($periodEnd->isFuture()) {
                    $daysRemaining = $now->diffInDays($periodEnd);
                    $totalDays = $subscription->current_period_start 
                        ? $subscription->current_period_start->diffInDays($periodEnd) 
                        : 30;
                    
                    // 비례 계산된 차액
                    $proratedAmount = ($priceDifference / $totalDays) * $daysRemaining;
                } else {
                    $proratedAmount = $priceDifference;
                }

                // 차액이 있는 경우 결제 페이지로 이동
                if ($proratedAmount != 0) {
                    // 차액 결제를 위한 결제 기록 생성
                    $orderId = 'plan_change_' . $subscription->id . '_' . \Illuminate\Support\Str::random(10);
                    
                    $payment = \App\Models\Payment::create([
                        'subscription_id' => $subscription->id,
                        'site_id' => $targetSite->id,
                        'toss_order_id' => $orderId,
                        'amount' => abs($proratedAmount),
                        'status' => 'pending',
                        'payment_type' => $proratedAmount > 0 ? 'plan_upgrade' : 'plan_downgrade',
                    ]);

                    // 플랜 변경 (결제 전에 미리 변경)
                    $subscription->update([
                        'plan_id' => $newPlan->id,
                    ]);

                    // 차액 결제를 위한 세션 저장
                    session([
                        'plan_change_subscription_id' => $subscription->id,
                        'plan_change_payment_id' => $payment->id,
                        'plan_change_amount' => abs($proratedAmount),
                        'plan_change_is_upgrade' => $proratedAmount > 0,
                    ]);
                    
                    return redirect()->route('payment.change-plan-checkout', [
                        'site' => $site->slug,
                        'userSite' => $targetSite->slug,
                        'subscription' => $subscription->id,
                        'order_id' => $orderId,
                    ])->with('info', $proratedAmount > 0 
                        ? '플랜이 상향 조정되었습니다. 차액 결제가 필요합니다.' 
                        : '플랜이 하향 조정되었습니다.');
                } else {
                    // 차액이 없으면 플랜 변경 후 바로 사이트 선택 페이지로 이동
                    $subscription->update([
                        'plan_id' => $newPlan->id,
                    ]);

                    session([
                        'server_capacity_subscription_id' => $subscription->id,
                        'server_capacity_is_plan_change' => true,
                    ]);

                    return redirect()->route('payment.select-server-capacity-site', ['site' => $site->slug])
                        ->with('success', '플랜이 변경되었습니다. 서버 용량을 적용할 사이트를 선택해주세요.');
                }
            } else {
                // 다음 결제일에 변경
                $subscription->update([
                    'plan_id' => $newPlan->id,
                ]);

                return redirect()->route('user-sites.change-plan', ['site' => $site->slug])
                    ->with('success', '다음 결제일(' . ($subscription->current_period_end ? $subscription->current_period_end->format('Y-m-d') : '-') . ')부터 새 플랜이 적용됩니다.');
            }
        }

        // 무료 플랜인 경우 바로 적용
        if ($newPlan->billing_type === 'free') {
            $subscription->update([
                'plan_id' => $newPlan->id,
            ]);

            // 사이트 플랜도 업데이트
            $targetSite->update([
                'plan' => $newPlan->slug,
            ]);

            return redirect()->route('user-sites.change-plan', ['site' => $site->slug])
                ->with('success', '플랜이 무료 플랜으로 변경되었습니다.');
        }

        // 유료 플랜인 경우 결제 진행
        // 차액 계산 (1회 결제 기준)
        $currentPlan = $subscription->plan;
        $currentPrice = (float) ($currentPlan->one_time_price ?? $currentPlan->price ?? 0);
        $newPrice = (float) ($newPlan->one_time_price ?? $newPlan->price ?? 0);
        $priceDifference = $newPrice - $currentPrice;

        // 결제 기록 생성
        $orderId = 'plan_change_' . $subscription->id . '_' . \Illuminate\Support\Str::random(10);
        
        $payment = \App\Models\Payment::create([
            'subscription_id' => $subscription->id,
            'site_id' => $targetSite->id,
            'toss_order_id' => $orderId,
            'amount' => abs($priceDifference) > 0 ? abs($priceDifference) : $newPrice,
            'status' => 'pending',
            'payment_type' => $priceDifference > 0 ? 'plan_upgrade' : ($priceDifference < 0 ? 'plan_downgrade' : 'plan_change'),
        ]);

        // 플랜 변경을 위한 세션 저장
        session([
            'plan_change_subscription_id' => $subscription->id,
            'plan_change_payment_id' => $payment->id,
            'plan_change_new_plan_id' => $newPlan->id,
            'plan_change_target_site_id' => $targetSite->id,
        ]);

        // 결제 페이지로 이동
        return redirect()->route('payment.change-plan-checkout', [
            'site' => $site->slug,
            'userSite' => $targetSite->slug,
            'subscription' => $subscription->id,
            'order_id' => $orderId,
        ])->with('info', $priceDifference > 0 
            ? '플랜이 상향 조정되었습니다. 차액 결제가 필요합니다.' 
            : ($priceDifference < 0 
                ? '플랜이 하향 조정되었습니다. 차액이 환불됩니다.' 
                : '플랜을 변경합니다.'));
    }

    /**
     * Show addon purchase page for a site.
     */
    public function showAddons(Site $site, Site $userSite)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        // 활성화된 추가 구매 상품 가져오기
        $addonProducts = AddonProduct::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('type')
            ->get();

        // 구독 정보 가져오기
        $subscription = Subscription::where('site_id', $userSite->id)
            ->where('user_id', $user->id)
            ->with('plan')
            ->first();

        // 현재 활성화된 추가 구매 내역
        $activeAddons = UserAddon::where('site_id', $userSite->id)
            ->where('status', 'active')
            ->with('addonProduct')
            ->get();

        return view('user-sites.addons', compact('site', 'userSite', 'addonProducts', 'subscription', 'activeAddons'));
    }

    /**
     * Show server upgrade page for a site.
     */
    public function showServerUpgrade(Site $site, Site $userSite)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        // 구독 정보 가져오기
        $subscription = Subscription::where('site_id', $userSite->id)
            ->where('user_id', $user->id)
            ->with('plan')
            ->first();

        if (!$subscription) {
            return redirect()->route('users.my-sites', ['site' => $site->slug])
                ->with('error', '구독 정보를 찾을 수 없습니다.');
        }

        // 무료 플랜인지 확인
        if ($subscription->plan->billing_type === 'free') {
            return redirect()->route('users.my-sites', ['site' => $site->slug])
                ->with('error', '서버 용량은 무료 플랜에 적용할 수 없습니다.');
        }

        // 서버 용량 플랜 가져오기
        $serverPlans = Plan::where('is_active', true)
            ->where('type', 'server')
            ->orderBy('price', 'asc')
            ->get();

        return view('user-sites.server-upgrade', compact('site', 'userSite', 'subscription', 'serverPlans'));
    }

    /**
     * Calculate actual storage usage for a site (MB).
     */
    protected function calculateStorageUsage(Site $site): int
    {
        $totalSize = 0;
        $basePath = storage_path('app/public');
        
        try {
            // 1. Post Attachments - 데이터베이스에서 파일 크기 합계
            $postAttachmentsSize = \App\Models\PostAttachment::whereHas('post', function($query) use ($site) {
                $query->where('site_id', $site->id);
            })->sum('file_size');
            $totalSize += $postAttachmentsSize;
            
            // 2. Banners - 이미지 파일 크기 계산
            $banners = \App\Models\Banner::where('site_id', $site->id)
                ->whereNotNull('image_path')
                ->get();
            foreach ($banners as $banner) {
                $filePath = $basePath . '/' . $banner->image_path;
                if (file_exists($filePath) && is_file($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }
            
            // 3. Popups - 이미지 파일 크기 계산
            $popups = \App\Models\Popup::where('site_id', $site->id)
                ->whereNotNull('image_path')
                ->get();
            foreach ($popups as $popup) {
                $filePath = $basePath . '/' . $popup->image_path;
                if (file_exists($filePath) && is_file($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }
            
            // 4. User Avatars - 해당 사이트의 사용자 아바타 크기 계산
            $siteUserIds = $site->users()->pluck('id')->toArray();
            if (!empty($siteUserIds)) {
                foreach ($siteUserIds as $userId) {
                    $avatarPath = $basePath . '/avatars/' . $userId;
                    if (is_dir($avatarPath)) {
                        $totalSize += $this->getDirectorySize($avatarPath);
                    }
                }
            }
            
            // 5. Site-specific upload directories
            $siteUploadPath = $basePath . '/uploads/sites/' . $site->id;
            if (is_dir($siteUploadPath)) {
                $totalSize += $this->getDirectorySize($siteUploadPath);
            }
            
            // 6. Banner directories for this site
            $bannerPath = $basePath . '/banners/' . $site->id;
            if (is_dir($bannerPath)) {
                $totalSize += $this->getDirectorySize($bannerPath);
            }
            
            // 7. Attachments for posts of this site (파일 시스템에서도 확인)
            $sitePostIds = $site->posts()->pluck('id')->toArray();
            if (!empty($sitePostIds)) {
                foreach ($sitePostIds as $postId) {
                    $attachmentPath = $basePath . '/attachments/' . $postId;
                    if (is_dir($attachmentPath)) {
                        $totalSize += $this->getDirectorySize($attachmentPath);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error calculating storage usage for site ' . $site->id . ': ' . $e->getMessage());
        }
        
        // Convert bytes to MB
        return (int) round($totalSize / 1024 / 1024);
    }

    /**
     * Get directory size recursively (bytes).
     */
    protected function getDirectorySize(string $directory): int
    {
        $size = 0;
        
        if (!is_dir($directory)) {
            return 0;
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error calculating directory size for ' . $directory . ': ' . $e->getMessage());
        }
        
        return $size;
    }

    /**
     * Update domain for a user site.
     */
    public function updateDomain(Request $request, Site $site, Site $userSite)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        $request->validate([
            'domain' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
        ], [
            'domain.regex' => '올바른 도메인 형식이 아닙니다. (예: example.com)',
        ]);

        // 도메인 정규화 (www 제거, 소문자 변환, 공백 제거)
        $domain = $request->input('domain');
        $cloudflareZoneId = null;
        $nameservers = null;
        
        if ($domain) {
            $domain = strtolower(trim($domain));
            $domain = preg_replace('/^www\./', '', $domain);
            
            // 중복 체크 (다른 사이트에서 사용 중인지)
            $existingSite = Site::where('domain', $domain)
                ->where('id', '!=', $userSite->id)
                ->where('status', 'active')
                ->first();
            
            if ($existingSite) {
                return back()->withErrors(['domain' => '이 도메인은 이미 다른 사이트에서 사용 중입니다.'])->withInput();
            }

            // Cloudflare에 도메인 추가 (자동화)
            $cloudflareService = app(CloudflareService::class);
            if ($cloudflareService->isEnabled()) {
                try {
                    $result = $cloudflareService->addDomain($domain);
                    if ($result) {
                        $cloudflareZoneId = $result['zone_id'];
                        $nameservers = $result['nameservers'];
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to add domain to Cloudflare', [
                        'domain' => $domain,
                        'error' => $e->getMessage(),
                    ]);
                    // Cloudflare 추가 실패해도 도메인은 저장 (수동 설정 가능)
                }
            }
        }

        $userSite->update([
            'domain' => $domain ?: null,
            'cloudflare_zone_id' => $cloudflareZoneId,
            'nameservers' => $nameservers ?: null, // Eloquent가 자동으로 JSON으로 변환 (casts 사용)
        ]);
        
        // 사이트 객체 새로고침 (캐시된 데이터 제거)
        $userSite->refresh();

        $message = $domain 
            ? '도메인이 성공적으로 연결되었습니다.' . ($nameservers ? ' 네임서버 정보를 확인하세요.' : '') 
            : '도메인이 제거되었습니다.';

        // AJAX 요청인 경우 JSON 응답 반환
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'domain' => $domain,
                'nameservers' => $nameservers ?: []
            ]);
        }

        // 일반 요청인 경우 리다이렉트
        return redirect()->route('admin.settings', ['site' => $userSite->slug])
            ->with('success', $message);
    }

    /**
     * Remove domain from a user site.
     */
    public function removeDomain(Site $site, Site $userSite)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $user = Auth::user();

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        $userSite->update([
            'domain' => null,
        ]);

        return redirect()->route('users.my-sites', ['site' => $site->slug])
            ->with('success', '도메인이 제거되었습니다.');
    }

    /**
     * SSO to site admin - 일반 사용자가 자신이 만든 사이트의 관리자로 자동 로그인
     */
    public function ssoToSiteAdmin(Site $site, Site $userSite)
    {
        $user = Auth::user();
        
        // 마스터 사이트인지 확인
        if (!$site || !$site->isMasterSite()) {
            abort(404);
        }
        
        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트에 대한 접근 권한이 없습니다.');
        }
        
        // 해당 사이트의 관리자 계정 찾기
        $admin = $userSite->users()
            ->where('role', 'admin')
            ->first();
        
        // 관리자 계정이 없으면 현재 사용자의 이메일로 관리자 계정 찾기 또는 생성
        if (!$admin) {
            $admin = $userSite->users()
                ->where('email', $user->email)
                ->first();
            
            if (!$admin) {
                // 관리자 계정 생성
                $admin = User::create([
                    'site_id' => $userSite->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'nickname' => $user->nickname ?? '운영자',
                    'password' => \Illuminate\Support\Facades\Hash::make(uniqid()), // 임시 비밀번호
                    'role' => 'admin',
                ]);
            } else {
                // 역할을 관리자로 업데이트
                if ($admin->role !== 'admin') {
                    $admin->update(['role' => 'admin']);
                }
            }
        }
        
        // 기존 세션에 마스터 사용자 정보 저장 (일반 사용자에서 온 경우)
        session(['is_sso_user' => true, 'sso_user_id' => $user->id, 'sso_from_site' => $site->id]);
        
        // 관리자 계정으로 로그인
        Auth::login($admin);
        
        // 세션 재생성
        request()->session()->regenerate();
        
        // 세션에 SSO 정보 다시 저장 (세션 재생성 후)
        session(['is_sso_user' => true, 'sso_user_id' => $user->id, 'sso_from_site' => $site->id]);
        
        // 사이트 설정 페이지로 리다이렉트
        return redirect()->route('admin.settings', ['site' => $userSite->slug])
            ->with('success', '관리자 페이지로 이동했습니다.');
    }
}

