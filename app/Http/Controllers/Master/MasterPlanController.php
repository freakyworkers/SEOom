<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth:master']);
    }

    /**
     * Display a listing of plans.
     */
    public function index()
    {
        // 무료 플랜과 유료 플랜, 서버 용량 플랜을 분리해서 가져오기
        $freePlans = Plan::where('billing_type', 'free')
            ->where('type', '!=', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $paidPlans = Plan::whereIn('billing_type', ['one_time', 'monthly'])
            ->where('type', '!=', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $serverPlans = Plan::where('type', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 각 플랜의 적용된 사이트 수 계산
        $freePlans->each(function ($plan) {
            $plan->sites_count = \App\Models\Site::where('plan', $plan->slug)->count();
        });

        $paidPlans->each(function ($plan) {
            $plan->sites_count = \App\Models\Site::where('plan', $plan->slug)->count();
        });

        $serverPlans->each(function ($plan) {
            $plan->sites_count = \App\Models\Site::where('plan', $plan->slug)->count();
        });

        return view('master.plans.index', compact('freePlans', 'paidPlans', 'serverPlans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        // 상위 플랜의 기능 가져오기 (기본 플랜과 브랜드 플랜)
        $parentPlansFeatures = [];
        
        // 기본 플랜(랜딩페이지) 기능 가져오기
        $landingPlan = Plan::where('type', 'landing')->first();
        if ($landingPlan) {
            $parentPlansFeatures['landing'] = $landingPlan->features ?? [];
        }
        
        // 브랜드 플랜 기능 가져오기
        $brandPlan = Plan::where('type', 'brand')->first();
        if ($brandPlan) {
            $parentPlansFeatures['brand'] = $brandPlan->features ?? [];
        }
        
        return view('master.plans.create', compact('parentPlansFeatures'));
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        // type이 'plan'인 경우 plan_type을 실제 type으로 사용
        $actualType = $request->type;
        if ($actualType === 'plan') {
            $actualType = $request->plan_type ?? 'landing';
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:plan,server',
            'plan_type' => 'required_if:type,plan|in:landing,brand,community',
            'billing_type' => 'required|in:free,one_time,monthly',
            'price' => 'nullable|numeric|min:0',
            'one_time_price' => 'nullable|numeric|min:0',
            'traffic_limit_mb' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 기본 플랜이 설정되면 다른 플랜의 is_default를 false로 변경
        if ($request->boolean('is_default')) {
            Plan::where('is_default', true)->update(['is_default' => false]);
        }

        // features 구조화
        $features = [];
        if ($request->has('features') && is_string($request->features)) {
            // JSON 문자열인 경우
            $features = json_decode($request->features, true) ?? [];
        } else {
            // 직접 배열로 전달된 경우
            $features = [
                'main_features' => $request->input('main_features', []),
                'board_types' => $request->input('board_types', []),
                'registration_features' => $request->input('registration_features', []),
                'sidebar_widget_types' => $request->input('sidebar_widget_types', []),
                'main_widget_types' => $request->input('main_widget_types', []),
                'custom_page_widget_types' => $request->input('custom_page_widget_types', []),
            ];
        }

        // limits 처리 (null 값 제거, '-'는 null로 처리)
        $limits = [];
        if ($request->has('limits')) {
            foreach ($request->input('limits', []) as $key => $value) {
                if ($value !== null && $value !== '' && $value !== '-') {
                    $limits[$key] = is_numeric($value) ? (int)$value : $value;
                }
            }
        }

        // traffic_limit_mb는 limits.traffic에서 가져오거나 직접 입력값 사용
        $trafficLimitMB = $request->input('traffic_limit_mb');
        if ($trafficLimitMB === null && isset($limits['traffic'])) {
            $trafficLimitMB = $limits['traffic'];
        }
        
        $plan = Plan::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'type' => $actualType,
            'billing_type' => $request->billing_type,
            'price' => $request->billing_type === 'monthly' ? ($request->price ?? 0) : 0,
            'one_time_price' => $request->billing_type === 'one_time' ? ($request->one_time_price ?? 0) : null,
            'traffic_limit_mb' => $trafficLimitMB ?? 0,
            'features' => $features,
            'limits' => $limits,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'is_default' => $request->boolean('is_default', false),
        ]);

        return redirect()->route('master.plans.show', $plan->id)
            ->with('success', '요금제가 성공적으로 생성되었습니다.');
    }

    /**
     * Display the specified plan.
     */
    public function show(Plan $plan)
    {
        $plan->load('sites');
        
        $stats = [
            'sites' => $plan->sites()->count(),
            'active_sites' => $plan->sites()->where('status', 'active')->count(),
        ];

        return view('master.plans.show', compact('plan', 'stats'));
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        // 상위 플랜의 기능 가져오기
        $parentPlansFeatures = [];
        
        if ($plan->type === 'brand' || $plan->type === 'community') {
            // 기본 플랜(랜딩페이지) 기능 가져오기
            $landingPlan = Plan::where('type', 'landing')->first();
            if ($landingPlan) {
                $parentPlansFeatures['landing'] = $landingPlan->features ?? [];
            }
        }
        
        if ($plan->type === 'community') {
            // 브랜드 플랜 기능 가져오기
            $brandPlan = Plan::where('type', 'brand')->first();
            if ($brandPlan) {
                $parentPlansFeatures['brand'] = $brandPlan->features ?? [];
            }
        }
        
        return view('master.plans.edit', compact('plan', 'parentPlansFeatures'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan)
    {
        // 검증 전에 plan_type이 없으면 기존 plan의 type에서 추출
        if ($request->type === 'plan') {
            if (!$request->has('plan_type') || $request->plan_type === null || $request->plan_type === '') {
                // 기존 plan의 type이 landing, brand, community 중 하나면 그대로 사용
                if (in_array($plan->type, ['landing', 'brand', 'community'])) {
                    $request->merge(['plan_type' => $plan->type]);
                } else {
                    // 기본값으로 landing 사용
                    $request->merge(['plan_type' => 'landing']);
                }
            }
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'type' => 'required|in:plan,server',
            'plan_type' => 'required_if:type,plan|in:landing,brand,community',
            'billing_type' => 'required|in:free,one_time,monthly',
            'price' => 'nullable|numeric|min:0',
            'one_time_price' => 'nullable|numeric|min:0',
            'traffic_limit_mb' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // type이 'plan'인 경우 plan_type을 실제 type으로 사용
        $actualType = $request->type;
        if ($actualType === 'plan') {
            // plan_type이 없으면 기존 plan의 type 사용
            $actualType = $request->plan_type ?? $plan->type;
        }

        // 기본 플랜이 설정되면 다른 플랜의 is_default를 false로 변경
        if ($request->boolean('is_default') && !$plan->is_default) {
            Plan::where('is_default', true)->update(['is_default' => false]);
        }

        // features 구조화
        $features = [];
        if ($request->has('features') && is_string($request->features)) {
            // JSON 문자열인 경우
            $features = json_decode($request->features, true) ?? [];
        } else {
            // 직접 배열로 전달된 경우
            $features = [
                'main_features' => $request->input('main_features', []),
                'board_types' => $request->input('board_types', []),
                'registration_features' => $request->input('registration_features', []),
                'sidebar_widget_types' => $request->input('sidebar_widget_types', []),
                'main_widget_types' => $request->input('main_widget_types', []),
                'custom_page_widget_types' => $request->input('custom_page_widget_types', []),
            ];
        }

        // limits 처리 (null 값 제거, '-'는 null로 처리)
        $limits = [];
        if ($request->has('limits')) {
            foreach ($request->input('limits', []) as $key => $value) {
                if ($value !== null && $value !== '' && $value !== '-') {
                    $limits[$key] = is_numeric($value) ? (int)$value : $value;
                }
            }
        }

        // traffic_limit_mb는 limits.traffic에서 가져오거나 직접 입력값 사용
        $trafficLimitMB = $request->input('traffic_limit_mb');
        if ($trafficLimitMB === null && isset($limits['traffic'])) {
            $trafficLimitMB = $limits['traffic'];
        } else if ($trafficLimitMB === null) {
            $trafficLimitMB = $plan->traffic_limit_mb ?? 0;
        }

        $plan->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'type' => $actualType,
            'billing_type' => $request->billing_type,
            'price' => $request->billing_type === 'monthly' ? ($request->price ?? 0) : 0,
            'one_time_price' => $request->billing_type === 'one_time' ? ($request->one_time_price ?? 0) : null,
            'traffic_limit_mb' => $trafficLimitMB,
            'features' => $features,
            'limits' => !empty($limits) ? $limits : $plan->limits,
            'sort_order' => $request->sort_order ?? $plan->sort_order,
            'is_active' => $request->boolean('is_active', $plan->is_active),
            'is_default' => $request->boolean('is_default', $plan->is_default),
        ]);

        return redirect()->route('master.plans.show', $plan->id)
            ->with('success', '요금제가 성공적으로 수정되었습니다.');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(Plan $plan)
    {
        // 사용 중인 사이트가 있으면 삭제 불가
        if ($plan->sites()->count() > 0) {
            return back()->with('error', '이 요금제를 사용 중인 사이트가 있어 삭제할 수 없습니다.');
        }

        $plan->delete();

        return redirect()->route('master.plans.index')
            ->with('success', '요금제가 성공적으로 삭제되었습니다.');
    }
}
