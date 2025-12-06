<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SiteProvisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UserSiteController extends Controller
{
    protected $provisionService;

    public function __construct(SiteProvisionService $provisionService)
    {
        $this->middleware('auth');
        $this->provisionService = $provisionService;
    }

    /**
     * Show plan selection page before creating a new site.
     * 이제는 무료 플랜으로 바로 사이트 생성 가능하도록 변경
     */
    public function selectPlan(Site $site)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        // 무료 플랜으로 바로 사이트 생성 페이지로 이동
        $freePlan = Plan::where('slug', 'free')->where('is_active', true)->first();
        if ($freePlan) {
            // 무료 플랜으로 바로 사이트 생성 가능
            return redirect()->route('user-sites.create', ['site' => $site->slug])
                ->with('info', '무료 플랜으로 사이트를 생성합니다.');
        }

        // 무료 플랜과 유료 플랜을 분리 (서버 용량 플랜 제외)
        $freePlans = Plan::where('is_active', true)
            ->where('billing_type', 'free')
            ->where('type', '!=', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $paidPlans = Plan::where('is_active', true)
            ->whereIn('billing_type', ['one_time', 'monthly'])
            ->where('type', '!=', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        return view('user-sites.select-plan', compact('site', 'freePlans', 'paidPlans'));
    }

    /**
     * Show the form for creating a new site.
     * 무료 플랜으로 바로 생성 가능, 또는 플랜 구매 후 생성
     */
    public function create(Request $request, Site $site)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        // 세션 또는 쿼리 파라미터에서 plan_id 확인
        $planId = $request->input('plan_id') ?? session('pending_site_plan_id');
        $plan = null;
        
        if ($planId) {
            // 플랜 선택 후 사이트 생성 (무료 플랜 포함)
            $plan = Plan::findOrFail($planId);
            
            // 무료 플랜인 경우 세션에 저장 (나중에 구독 생성 시 사용)
            if ($plan->billing_type === 'free') {
                session(['pending_site_plan_id' => $plan->id]);
            }
        } else {
            // 기본 무료 플랜으로 사이트 생성
            $plan = Plan::where('slug', 'free')->where('is_active', true)->first();
            if (!$plan) {
                return redirect()->route('user-sites.select-plan', ['site' => $site->slug])
                    ->with('error', '무료 플랜을 찾을 수 없습니다.');
            }
        }

        return view('user-sites.create', compact('site', 'plan'));
    }

    /**
     * Store a newly created site.
     */
    public function store(Request $request, Site $site)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sites,slug',
            'login_method' => 'required|in:email,username',
            'admin_username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/', // 영문, 숫자, 언더스코어만 허용
                function ($attribute, $value, $fail) {
                    // 사이트 생성 전이므로 임시로 체크 (실제로는 사이트 생성 후 해당 사이트 내에서만 중복 체크)
                    // 여기서는 기본적인 형식만 검증
                },
            ],
            'admin_password' => 'required|string|min:8|confirmed',
        ], [
            'admin_username.regex' => '아이디는 영문, 숫자, 언더스코어(_)만 사용할 수 있습니다.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // 세션 또는 쿼리 파라미터에서 plan_id 확인
        $planId = $request->input('plan_id') ?? session('pending_site_plan_id');
        $plan = null;
        
        if ($planId) {
            // 플랜 선택 후 사이트 생성 (무료 플랜 포함)
            $plan = Plan::findOrFail($planId);
        } else {
            // 기본 무료 플랜으로 사이트 생성
            $plan = Plan::where('slug', 'free')->where('is_active', true)->first();
            if (!$plan) {
                return redirect()->route('user-sites.select-plan', ['site' => $site->slug])
                    ->with('error', '무료 플랜을 찾을 수 없습니다.');
            }
        }

        $data = $request->all();
        if (empty($data['slug']) || $data['slug'] === 'null' || $data['slug'] === null) {
            $data['slug'] = Str::slug($data['name']);
        }

        // 기본값 설정
        $data['plan'] = $plan->slug; // 선택한 플랜 사용
        $data['status'] = 'active';
        $data['is_master_site'] = false;
        $data['created_by'] = $user->id; // 사이트 생성자 저장
        $data['domain'] = null; // 도메인은 나중에 설정

        // 관리자 계정 정보
        $data['admin_name'] = $user->name;
        $data['admin_email'] = $user->email;
        $data['admin_username'] = $request->input('admin_username');
        $data['admin_password'] = $request->input('admin_password');
        $data['login_method'] = $request->input('login_method', 'email');

        try {
            $newSite = $this->provisionService->provision($data);
        } catch (\Exception $e) {
            return back()->withErrors(['admin_username' => $e->getMessage()])->withInput();
        }

        // 세션에서 구독 정보 가져오기 (유료 플랜 결제 후인 경우)
        $subscriptionId = session('pending_site_subscription_id');
        if ($subscriptionId) {
            $subscription = Subscription::findOrFail($subscriptionId);
            // 구독을 새로 생성한 사이트에 연결
            $subscription->update([
                'site_id' => $newSite->id,
            ]);
        } elseif ($plan->billing_type === 'free') {
            // 무료 플랜인 경우 구독 생성 (이미 active 상태로 생성됨)
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            $subscription = $subscriptionService->createSubscription(
                $newSite,
                $plan,
                0, // trial 제거
                $user
            );
        }

        // 세션 정리
        session()->forget(['pending_site_plan_id', 'pending_site_subscription_id']);

        // 서브도메인 URL 생성
        $masterDomain = config('app.master_domain', 'seoomweb.com');
        $subdomainUrl = 'https://' . $newSite->slug . '.' . $masterDomain;
        
        // 생성된 사이트의 서브도메인으로 리다이렉트
        return redirect($subdomainUrl)
            ->with('success', '사이트가 성공적으로 생성되었습니다. 서브도메인으로 접근합니다.');
    }
}

