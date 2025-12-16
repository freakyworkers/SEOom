<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /**
     * Show store page with plans and server capacity options.
     */
    public function index(Site $site)
    {
        // 마스터 사이트인지 확인
        if (!$site->isMasterSite()) {
            abort(404);
        }

        // 무료 플랜 가져오기
        $freePlans = Plan::where('is_active', true)
            ->where('billing_type', 'free')
            ->where('type', '!=', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        // 유료 플랜 가져오기
        $paidPlans = Plan::where('is_active', true)
            ->whereIn('billing_type', ['one_time', 'monthly'])
            ->where('type', '!=', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        // 서버 용량 플랜 가져오기
        $serverPlans = Plan::where('is_active', true)
            ->where('type', 'server')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        // 로그인한 사용자의 사이트 목록 가져오기 (서버 용량 구독 가능한 사이트)
        $userSites = collect([]);
        if (Auth::check()) {
            $userSites = Site::where('created_by', Auth::id())
                ->where('is_master_site', false)
                ->with(['subscription.plan'])
                ->get()
                ->filter(function ($userSite) {
                    // 구독이 있고, 무료 플랜이 아닌 사이트만
                    $subscription = $userSite->subscription;
                    if (!$subscription || !$subscription->plan) {
                        return false;
                    }
                    return $subscription->plan->billing_type !== 'free';
                });
        }

        return view('store.index', compact('site', 'freePlans', 'paidPlans', 'serverPlans', 'userSites'));
    }
}

