<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\AddonProduct;
use App\Models\UserAddon;
use App\Services\SubscriptionService;
use App\Services\TossPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $subscriptionService;
    protected $tossPaymentService;

    public function __construct(SubscriptionService $subscriptionService, TossPaymentService $tossPaymentService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->tossPaymentService = $tossPaymentService;
    }

    /**
     * Show subscription page for a plan.
     */
    public function subscribe(Plan $plan)
    {
        // Get master site
        $site = Site::getMasterSite();
        if (!$site || !$site->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = auth()->user();
        $userSites = collect([]);

        // 서버 용량 플랜인 경우 사용자 사이트 목록 가져오기 (무료 플랜 제외)
        if ($plan->type === 'server' && $user) {
            $userSites = Site::where('created_by', $user->id)
                ->where('is_master_site', false)
                ->with(['subscription.plan'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($userSite) {
                    // 무료 플랜이 아닌 사이트만 필터링
                    if ($userSite->subscription && $userSite->subscription->plan) {
                        return $userSite->subscription->plan->billing_type !== 'free';
                    }
                    // 구독이 없는 사이트도 포함 (무료 플랜이 아닐 수 있음)
                    return true;
                });
        }

        return view('payment.subscribe', compact('site', 'plan', 'userSites'));
    }

    /**
     * Process subscription payment.
     */
    public function processSubscription(Request $request, Plan $plan)
    {
        // Get master site
        $site = Site::getMasterSite();
        if (!$site || !$site->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 사이트 생성 전 결제인 경우 (세션에 플래그 확인)
        $isSiteCreation = $request->has('create_site') || session('pending_site_plan_id') === $plan->id;

        // 사이트 생성 전 결제인 경우, 임시로 마스터 사이트에 구독 생성 (나중에 사이트 생성 시 이동)
        $targetSite = $isSiteCreation ? $site : $user->site;

        // 서버 용량 플랜인 경우 선택한 사이트로 구독 생성 및 결제 진행
        if ($plan->type === 'server') {
            $request->validate([
                'target_site_id' => 'required|exists:sites,id',
            ]);

            $targetSite = Site::findOrFail($request->target_site_id);

            // 사용자가 소유한 사이트인지 확인
            if ($targetSite->created_by !== $user->id) {
                return redirect()->route('payment.subscribe', ['plan' => $plan->slug])
                    ->with('error', '이 사이트를 변경할 권한이 없습니다.');
            }

            // 무료 플랜인지 확인
            if ($targetSite->subscription && $targetSite->subscription->plan && $targetSite->subscription->plan->billing_type === 'free') {
                return redirect()->route('payment.subscribe', ['plan' => $plan->slug])
                    ->with('error', '서버 용량은 무료 플랜에 적용할 수 없습니다.');
            }

            // 구독 생성
            $subscription = $this->subscriptionService->createSubscription(
                $targetSite,
                $plan,
                0, // trial 제거
                $user
            );

            // 결제 기록 생성
            $orderId = 'order_' . Str::random(16);
            $planPrice = $plan->price ?? 0;

            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'site_id' => $targetSite->id,
                'toss_order_id' => $orderId,
                'amount' => $planPrice,
                'status' => 'pending',
                'payment_type' => 'subscription',
            ]);

            // 세션에 저장 (결제 완료 후 서버 용량 적용을 위해)
            session([
                'server_capacity_subscription_id' => $subscription->id,
                'server_capacity_payment_id' => $payment->id,
                'server_capacity_target_site_id' => $targetSite->id,
            ]);

            // 결제 페이지로 이동
            return redirect()->route('payment.checkout', [
                'plan' => $plan->slug,
                'order_id' => $orderId,
                'subscription_id' => $subscription->id,
            ]);
        }

        // Create subscription
        $subscription = $this->subscriptionService->createSubscription(
            $targetSite,
            $plan,
            0, // trial 제거
            $user
        );

        // 사이트 생성 전 결제인 경우 세션에 저장
        if ($isSiteCreation) {
            session([
                'pending_site_plan_id' => $plan->id,
                'pending_site_subscription_id' => $subscription->id,
            ]);
        }

        // Create payment record
        $orderId = 'order_' . Str::random(16);
        
        // 플랜 결제 타입에 따라 가격 결정
        $planPrice = 0;
        if ($plan->billing_type === 'one_time') {
            $planPrice = $plan->one_time_price ?? 0;
        } elseif ($plan->billing_type === 'monthly') {
            $planPrice = $plan->price ?? 0;
        }

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'site_id' => $targetSite->id,
            'toss_order_id' => $orderId,
            'amount' => $planPrice,
            'status' => 'pending',
            'payment_type' => 'subscription',
        ]);

        // 사이트 생성 전 결제인 경우 반드시 결제를 진행해야 함
        if ($isSiteCreation) {
            // 결제 정보 입력 페이지로 리다이렉트
            return redirect()->route('payment.checkout', [
                'plan' => $plan->slug,
                'order_id' => $orderId,
                'subscription_id' => $subscription->id,
            ]);
        }

        // 일반 구독의 경우 결제 진행
        return redirect()->route('payment.checkout', [
            'plan' => $plan->slug,
            'order_id' => $orderId,
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Show checkout page for payment.
     */
    public function checkout(Request $request)
    {
        // Get master site
        $site = Site::getMasterSite();
        if (!$site || !$site->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        $orderId = $request->input('order_id');
        $subscriptionId = $request->input('subscription_id');
        $planSlug = $request->input('plan');

        if (!$orderId || !$subscriptionId || !$planSlug) {
            return redirect()->route('user-sites.select-plan', ['site' => $site->slug])
                ->with('error', '결제 정보가 올바르지 않습니다.');
        }

        $subscription = Subscription::findOrFail($subscriptionId);
        $plan = $subscription->plan;
        $payment = Payment::where('toss_order_id', $orderId)->first();

        if (!$payment) {
            return redirect()->route('user-sites.select-plan', ['site' => $site->slug])
                ->with('error', '결제 정보를 찾을 수 없습니다.');
        }

        // 세션에 결제 정보 저장
        session([
            'pending_site_plan_id' => $plan->id,
            'pending_site_subscription_id' => $subscription->id,
            'pending_payment_order_id' => $orderId,
        ]);

        return view('payment.checkout', compact('site', 'plan', 'subscription', 'payment', 'orderId'));
    }

    /**
     * Show checkout page for plan change payment.
     */
    public function changePlanCheckout(Request $request)
    {
        // Get master site
        $masterSite = Site::getMasterSite();
        if (!$masterSite || !$masterSite->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 쿼리 파라미터에서 정보 가져오기
        $siteSlug = $request->input('site');
        $userSiteSlug = $request->input('userSite');
        $subscriptionId = $request->input('subscription');
        $orderId = $request->input('order_id');

        if (!$siteSlug || !$userSiteSlug || !$subscriptionId || !$orderId) {
            return redirect()->route('user-sites.index', ['site' => $masterSite->slug])
                ->with('error', '결제 정보가 올바르지 않습니다.');
        }

        $site = Site::where('slug', $siteSlug)->firstOrFail();
        $userSite = Site::where('slug', $userSiteSlug)->firstOrFail();

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        $subscription = Subscription::findOrFail($subscriptionId);
        
        // 세션에서 플랜 변경 정보 가져오기
        $paymentId = session('plan_change_payment_id');
        if (!$paymentId) {
            return redirect()->route('user-sites.index', ['site' => $site->slug])
                ->with('error', '결제 정보를 찾을 수 없습니다.');
        }

        $payment = Payment::findOrFail($paymentId);
        $changeAmount = $payment->amount;
        $isUpgrade = $payment->payment_type === 'plan_upgrade';

        $plan = $subscription->plan;

        return view('payment.change-plan-checkout', compact('site', 'userSite', 'subscription', 'plan', 'orderId', 'changeAmount', 'isUpgrade', 'payment'));
    }

    /**
     * Handle payment success callback from Toss Payments.
     */
    public function success(Request $request)
    {
        $paymentKey = $request->input('paymentKey');
        $orderId = $request->input('orderId');
        $amount = $request->input('amount');

        if (!$paymentKey || !$orderId || !$amount) {
            return redirect('/')
                ->with('error', '결제 정보가 올바르지 않습니다.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        try {
            // Confirm payment with Toss
            $tossResponse = $this->tossPaymentService->confirmPayment(
                $paymentKey,
                $orderId,
                (int) $amount
            );

            // Find payment record
            $payment = Payment::where('toss_order_id', $orderId)->first();
            if (!$payment) {
                Log::error('Payment record not found', ['order_id' => $orderId]);
                return redirect('/')
                    ->with('error', '결제 정보를 찾을 수 없습니다.');
            }

            // Update payment record
            $payment->update([
                'toss_payment_key' => $paymentKey,
                'status' => 'paid',
                'paid_at' => now(),
                'toss_response' => $tossResponse,
            ]);

            $masterSite = Site::getMasterSite();
            
            // Update subscription
            $subscription = $payment->subscription;
            if ($subscription) {
                // 정기 결제를 위한 빌링 키 발급 (첫 결제 시)
                if (!$subscription->toss_billing_key && isset($tossResponse['paymentKey'])) {
                    try {
                        $customerKey = 'customer_' . $subscription->user_id;
                        $billingKeyResponse = $this->tossPaymentService->createBillingKey(
                            $customerKey,
                            $tossResponse['paymentKey']
                        );
                        
                        if (isset($billingKeyResponse['billingKey'])) {
                            $subscription->update([
                                'toss_billing_key' => $billingKeyResponse['billingKey'],
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Billing key creation failed', [
                            'subscription_id' => $subscription->id,
                            'error' => $e->getMessage(),
                        ]);
                        // 빌링 키 발급 실패해도 결제는 진행
                    }
                }

                // 플랜 결제 타입에 따라 처리
                $plan = $subscription->plan;
                if ($plan && $plan->billing_type === 'one_time') {
                    // 1회성 결제인 경우 이미 active 상태
                } elseif ($plan && $plan->billing_type === 'monthly') {
                    // 월간 결제인 경우 이미 active 상태
                } else {
                    // 무료 플랜은 이미 active 상태
                }

                // Save billing key if provided in response (월간 결제인 경우에만)
                if (isset($tossResponse['billingKey'])) {
                    $subscription->update([
                        'toss_billing_key' => $tossResponse['billingKey'],
                    ]);
                }

                // 서버 용량 플랜인 경우 서버 용량 적용
                if ($plan && $plan->type === 'server') {
                    // 세션에서 대상 사이트 ID 가져오기
                    $targetSiteId = session('server_capacity_target_site_id');
                    if ($targetSiteId) {
                        $targetSite = Site::find($targetSiteId);
                        if ($targetSite && $targetSite->created_by === $user->id) {
                            // 서버 용량 적용
                            $storageLimit = $plan->limits['storage'] ?? 0;
                            $trafficLimit = $plan->traffic_limit_mb ?? 0;

                            // 기존 용량에 추가
                            $currentStorageLimit = $targetSite->storage_limit_mb ?? 0;
                            $currentTrafficLimit = $targetSite->traffic_limit_mb ?? 0;

                            $targetSite->update([
                                'storage_limit_mb' => $currentStorageLimit + $storageLimit,
                                'traffic_limit_mb' => $currentTrafficLimit + $trafficLimit,
                            ]);

                            // 구독을 대상 사이트에 연결
                            $subscription->update([
                                'site_id' => $targetSite->id,
                            ]);

                            // 세션 정리
                            session()->forget([
                                'server_capacity_subscription_id',
                                'server_capacity_payment_id',
                                'server_capacity_target_site_id',
                                'server_capacity_plan_id',
                                'server_capacity_is_new_subscription',
                            ]);

                            return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                                ->with('success', '서버 용량 플랜 결제가 완료되었고 서버 용량이 적용되었습니다.');
                        }
                    }

                    // 대상 사이트가 없으면 사이트 선택 페이지로 이동
                    session([
                        'server_capacity_subscription_id' => $subscription->id,
                        'server_capacity_payment_id' => $payment->id,
                    ]);
                    return redirect()->route('payment.select-server-capacity-site', ['site' => $masterSite->slug])
                        ->with('success', '결제가 완료되었습니다. 서버 용량을 적용할 사이트를 선택해주세요.');
                }

                // 사이트 생성 전 결제인 경우 세션에 저장하고 사이트 생성 페이지로 리다이렉트
                if (session('pending_site_plan_id')) {
                    session([
                        'pending_site_plan_id' => $subscription->plan_id,
                        'pending_site_subscription_id' => $subscription->id,
                    ]);
                    return redirect()->route('user-sites.create', ['site' => $masterSite->slug])
                        ->with('success', '결제가 완료되었습니다. 이제 사이트 정보를 입력해주세요.');
                }

                // 플랜 변경 결제인 경우
                if (session('plan_change_subscription_id')) {
                    // 플랜 변경 결제 기록 업데이트
                    $paymentId = session('plan_change_payment_id');
                    if ($paymentId) {
                        $planChangePayment = Payment::find($paymentId);
                        if ($planChangePayment) {
                            $planChangePayment->update([
                                'toss_payment_key' => $paymentKey,
                                'status' => 'paid',
                                'paid_at' => now(),
                                'toss_response' => $tossResponse,
                            ]);
                        }
                    }
                    
                    // 실제 플랜 변경 처리
                    $newPlanId = session('plan_change_new_plan_id');
                    $targetSiteId = session('plan_change_target_site_id');
                    
                    if ($newPlanId && $targetSiteId) {
                        $newPlan = \App\Models\Plan::find($newPlanId);
                        $targetSite = Site::find($targetSiteId);
                        
                        if ($newPlan && $targetSite && $subscription) {
                            // 서버 용량 플랜으로 변경한 경우 사이트 선택 페이지로 이동
                            if ($newPlan->type === 'server') {
                                session([
                                    'server_capacity_subscription_id' => $subscription->id,
                                    'server_capacity_payment_id' => $paymentId,
                                    'server_capacity_is_plan_change' => true,
                                ]);
                                return redirect()->route('payment.select-server-capacity-site', ['site' => $masterSite->slug])
                                    ->with('success', '플랜 변경 결제가 완료되었습니다. 서버 용량을 적용할 사이트를 선택해주세요.');
                            }
                            
                            // 플랜 변경 적용
                            $subscription->update([
                                'plan_id' => $newPlan->id,
                            ]);
                            
                            // 사이트 플랜도 업데이트
                            $targetSite->update([
                                'plan' => $newPlan->slug,
                            ]);
                        }
                    }
                    
                    // 세션 정리
                    session()->forget([
                        'plan_change_subscription_id', 
                        'plan_change_payment_id', 
                        'plan_change_amount', 
                        'plan_change_is_upgrade',
                        'plan_change_new_plan_id',
                        'plan_change_target_site_id',
                    ]);
                    
                    return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                        ->with('success', '플랜 변경 결제가 완료되었습니다.');
                }

                // 추가 구매 결제인 경우
                if (session('addon_purchase_product_id')) {
                    $addonProductId = session('addon_purchase_product_id');
                    $userSiteId = session('addon_purchase_user_site_id');
                    $subscriptionId = session('addon_purchase_subscription_id');
                    $paymentId = session('addon_purchase_payment_id');

                    if ($addonProductId && $userSiteId && $subscriptionId) {
                        $addonProduct = AddonProduct::find($addonProductId);
                        $userSite = Site::find($userSiteId);
                        $subscription = Subscription::find($subscriptionId);

                        if ($addonProduct && $userSite && $subscription) {
                            // 결제 기록 업데이트
                            if ($paymentId) {
                                $addonPayment = Payment::find($paymentId);
                                if ($addonPayment) {
                                    $addonPayment->update([
                                        'toss_payment_key' => $paymentKey,
                                        'status' => 'paid',
                                        'paid_at' => now(),
                                        'toss_response' => $tossResponse,
                                    ]);
                                }
                            }

                            // 추가 구매 내역 생성
                            $expiresAt = null;
                            $amountMb = $addonProduct->amount_mb;
                            $price = $addonProduct->price;
                            
                            // 옵션 ID가 세션에 있으면 옵션 정보 사용
                            $optionId = session('addon_purchase_option_id');
                            if ($optionId) {
                                $option = \App\Models\AddonProductOption::find($optionId);
                                if ($option && $option->addon_product_id === $addonProduct->id) {
                                    $amountMb = $option->amount_mb ?? $addonProduct->amount_mb;
                                    $price = $option->price;
                                }
                            }
                            
                            if ($addonProduct->billing_cycle === 'monthly') {
                                // 플랜의 current_period_end와 맞춰서 설정
                                if ($subscription->current_period_end) {
                                    $expiresAt = $subscription->current_period_end->copy();
                                } else {
                                    // current_period_end가 없으면 다음 달로 설정
                                    $expiresAt = Carbon::now()->addMonth();
                                }
                            }

                            UserAddon::create([
                                'user_id' => $subscription->user_id,
                                'site_id' => $userSite->id,
                                'addon_product_id' => $addonProduct->id,
                                'subscription_id' => $subscription->id,
                                'amount_mb' => $amountMb,
                                'price' => $price,
                                'status' => 'active',
                                'expires_at' => $expiresAt,
                            ]);

                            // 추가 구매 상품 타입에 따라 사이트 기능 활성화
                            $this->activateAddonFeature($userSite, $addonProduct);

                            // 사이트 용량 제한 업데이트 (실제로는 getTotalStorageLimit/getTotalTrafficLimit에서 계산)
                            // 여기서는 별도 업데이트 불필요 (관계를 통해 자동 계산)

                            session()->forget([
                                'addon_purchase_user_site_id',
                                'addon_purchase_product_id',
                                'addon_purchase_payment_id',
                                'addon_purchase_subscription_id',
                                'addon_purchase_option_id',
                            ]);

                            return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                                ->with('success', '추가 구매가 완료되었습니다.');
                        }
                    }
                }
            }

            return redirect()->route('payment.success-page', ['site' => $masterSite->slug])
                ->with('success', '결제가 완료되었습니다.');

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'payment_key' => $paymentKey,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('payment.fail-page')
                ->with('error', '결제 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment failure callback from Toss Payments.
     */
    public function fail(Request $request)
    {
        $code = $request->input('code');
        $message = $request->input('message');
        $orderId = $request->input('orderId');

        Log::error('Payment failed', [
            'code' => $code,
            'message' => $message,
            'order_id' => $orderId,
        ]);

        // Update payment record if exists
        if ($orderId) {
            $payment = Payment::where('toss_order_id', $orderId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $message,
                ]);

                // Mark subscription as past due
                if ($payment->subscription) {
                    $this->subscriptionService->markAsPastDue($payment->subscription);
                }
            }
        }

        return redirect()->route('payment.fail-page')
            ->with('error', '결제에 실패했습니다: ' . $message);
    }

    /**
     * Process addon purchase.
     */
    public function processAddon(Request $request, Site $userSite, AddonProduct $addonProduct)
    {
        // Get master site
        $masterSite = Site::getMasterSite();
        if (!$masterSite || !$masterSite->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        // 구독 정보 확인
        $subscription = Subscription::where('site_id', $userSite->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return redirect()->route('user-sites.addons', ['site' => $masterSite->slug, 'userSite' => $userSite->slug])
                ->with('error', '구독 정보를 찾을 수 없습니다.');
        }

        // 옵션 확인
        $optionId = $request->input('option_id');
        $option = null;
        $amount = $addonProduct->price;
        
        if ($optionId) {
            $option = \App\Models\AddonProductOption::find($optionId);
            if ($option && $option->addon_product_id === $addonProduct->id && $option->is_active) {
                $amount = $option->price;
            } else {
                return redirect()->route('user-sites.addons', ['site' => $masterSite->slug, 'userSite' => $userSite->slug])
                    ->with('error', '선택한 옵션을 찾을 수 없습니다.');
            }
        }

        // 결제 기록 생성
        $orderId = 'addon_' . $addonProduct->id . '_' . $userSite->id . '_' . Str::random(10);
        
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'site_id' => $userSite->id,
            'toss_order_id' => $orderId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_type' => 'addon',
        ]);

        // 세션에 추가 구매 정보 저장
        session([
            'addon_purchase_user_site_id' => $userSite->id,
            'addon_purchase_product_id' => $addonProduct->id,
            'addon_purchase_payment_id' => $payment->id,
            'addon_purchase_subscription_id' => $subscription->id,
            'addon_purchase_option_id' => $optionId,
        ]);

        // 결제 페이지로 리다이렉트 (ID 사용)
        return redirect()->route('payment.addon-checkout', [
            'site' => $masterSite->slug,
            'userSite' => $userSite->slug,
            'addonProduct' => $addonProduct->id,
            'order_id' => $orderId,
        ]);
    }

    /**
     * Show addon checkout page.
     */
    public function addonCheckout(Request $request)
    {
        // Get master site
        $masterSite = Site::getMasterSite();
        if (!$masterSite || !$masterSite->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 쿼리 파라미터에서 값 가져오기
        $userSiteSlug = $request->input('userSite');
        $addonProductId = $request->input('addonProduct');
        $orderId = $request->input('order_id');

        if (!$userSiteSlug || !$addonProductId || !$orderId) {
            return redirect()->route('store.plugins')
                ->with('error', '결제 정보가 올바르지 않습니다.');
        }

        // 모델 찾기
        $userSite = Site::where('slug', $userSiteSlug)->first();
        if (!$userSite) {
            abort(404, '사이트를 찾을 수 없습니다.');
        }

        $addonProduct = AddonProduct::find($addonProductId);
        if (!$addonProduct) {
            abort(404, '플러그인을 찾을 수 없습니다.');
        }

        // 사용자가 소유한 사이트인지 확인
        if ($userSite->created_by !== $user->id) {
            abort(403, '이 사이트를 변경할 권한이 없습니다.');
        }

        return view('payment.addon-checkout', compact('masterSite', 'userSite', 'addonProduct', 'orderId'));
    }

    /**
     * Show payment success page.
     */
    public function successPage()
    {
        $site = Site::getMasterSite();
        return view('payment.success', compact('site'));
    }

    /**
     * Show payment failure page.
     */
    public function failPage()
    {
        $site = Site::getMasterSite();
        return view('payment.fail', compact('site'));
    }

    /**
     * Activate addon feature for site.
     */
    private function activateAddonFeature(Site $site, AddonProduct $addonProduct)
    {
        // 기존 custom_features 가져오기
        $customFeatures = $site->getSetting('custom_features', null);
        $customFeaturesArray = $customFeatures !== null 
            ? (is_array($customFeatures) ? $customFeatures : json_decode($customFeatures, true))
            : [];

        // 플랜의 기본 features 가져오기
        $planModel = $site->planModel();
        $planFeatures = $planModel ? ($planModel->features ?? []) : [];

        // custom_features 초기화 (플랜 features로 시작)
        if (empty($customFeaturesArray)) {
            $customFeaturesArray = [
                'main_features' => $planFeatures['main_features'] ?? [],
                'board_types' => $planFeatures['board_types'] ?? [],
                'registration_features' => $planFeatures['registration_features'] ?? [],
                'sidebar_widget_types' => $planFeatures['sidebar_widget_types'] ?? [],
                'main_widget_types' => $planFeatures['main_widget_types'] ?? [],
                'custom_page_widget_types' => $planFeatures['custom_page_widget_types'] ?? [],
            ];
        } else {
            // 기존 custom_features가 있으면 플랜 features와 병합 (플랜 features + 기존 custom features)
            $customFeaturesArray['main_features'] = array_unique(array_merge(
                $planFeatures['main_features'] ?? [],
                $customFeaturesArray['main_features'] ?? []
            ));
            $customFeaturesArray['board_types'] = array_unique(array_merge(
                $planFeatures['board_types'] ?? [],
                $customFeaturesArray['board_types'] ?? []
            ));
            $customFeaturesArray['registration_features'] = array_unique(array_merge(
                $planFeatures['registration_features'] ?? [],
                $customFeaturesArray['registration_features'] ?? []
            ));
            $customFeaturesArray['sidebar_widget_types'] = array_unique(array_merge(
                $planFeatures['sidebar_widget_types'] ?? [],
                $customFeaturesArray['sidebar_widget_types'] ?? []
            ));
            $customFeaturesArray['main_widget_types'] = array_unique(array_merge(
                $planFeatures['main_widget_types'] ?? [],
                $customFeaturesArray['main_widget_types'] ?? []
            ));
            $customFeaturesArray['custom_page_widget_types'] = array_unique(array_merge(
                $planFeatures['custom_page_widget_types'] ?? [],
                $customFeaturesArray['custom_page_widget_types'] ?? []
            ));
        }

        // 추가 구매 상품 타입에 따라 기능 추가
        switch ($addonProduct->type) {
            case 'feature_crawler':
                if (!in_array('crawlers', $customFeaturesArray['main_features'])) {
                    $customFeaturesArray['main_features'][] = 'crawlers';
                }
                break;
            case 'feature_event_application':
                if (!in_array('event_application', $customFeaturesArray['main_features'])) {
                    $customFeaturesArray['main_features'][] = 'event_application';
                }
                break;
            case 'feature_point_exchange':
                if (!in_array('point_exchange', $customFeaturesArray['main_features'])) {
                    $customFeaturesArray['main_features'][] = 'point_exchange';
                }
                break;
            case 'board_type_event':
                if (!in_array('event', $customFeaturesArray['board_types'])) {
                    $customFeaturesArray['board_types'][] = 'event';
                }
                break;
            case 'registration_referral':
                if (!in_array('referrer', $customFeaturesArray['registration_features'])) {
                    $customFeaturesArray['registration_features'][] = 'referrer';
                }
                break;
            case 'feature_point_message':
                if (!in_array('point_message', $customFeaturesArray['registration_features'])) {
                    $customFeaturesArray['registration_features'][] = 'point_message';
                }
                break;
        }

        // custom_features 저장
        $site->setSetting('custom_features', $customFeaturesArray);
    }

    /**
     * Show server capacity site selection page.
     */
    public function selectServerCapacitySite(Site $site)
    {
        // Get master site
        $masterSite = Site::getMasterSite();
        if (!$masterSite || !$masterSite->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        // 새로운 구독인 경우 플랜 정보 가져오기
        $planId = session('server_capacity_plan_id');
        $isNewSubscription = session('server_capacity_is_new_subscription', false);
        $subscription = null;
        $plan = null;

        if ($isNewSubscription && $planId) {
            // 새로운 구독인 경우
            $plan = Plan::findOrFail($planId);
            if ($plan->type !== 'server') {
                return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                    ->with('error', '서버 용량 플랜이 아닙니다.');
            }
        } else {
            // 기존 구독(플랜 변경)인 경우
            $subscriptionId = session('server_capacity_subscription_id');
            if (!$subscriptionId) {
                return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                    ->with('error', '서버 용량 플랜 정보를 찾을 수 없습니다.');
            }

            $subscription = Subscription::with('plan')->findOrFail($subscriptionId);
            $plan = $subscription->plan;

            if (!$plan || $plan->type !== 'server') {
                return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                    ->with('error', '서버 용량 플랜이 아닙니다.');
            }
        }

        // 사용자가 소유한 사이트 목록 가져오기
        $userSites = Site::where('created_by', $user->id)
            ->where('is_master_site', false)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($userSites->isEmpty()) {
            return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                ->with('error', '서버 용량을 적용할 사이트가 없습니다. 먼저 사이트를 생성해주세요.');
        }

        $isPlanChange = session('server_capacity_is_plan_change', false);

        return view('payment.select-server-capacity-site', compact('site', 'masterSite', 'subscription', 'plan', 'userSites', 'isPlanChange', 'isNewSubscription'));
    }

    /**
     * Apply server capacity plan to selected site.
     */
    public function applyServerCapacity(Request $request, Site $site)
    {
        // Get master site
        $masterSite = Site::getMasterSite();
        if (!$masterSite || !$masterSite->isMasterSite()) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '로그인이 필요합니다.');
        }

        $request->validate([
            'target_site_id' => 'required|exists:sites,id',
        ]);

        // 새로운 구독인 경우
        $planId = session('server_capacity_plan_id');
        $isNewSubscription = session('server_capacity_is_new_subscription', false);
        $subscription = null;
        $plan = null;

        if ($isNewSubscription && $planId) {
            // 새로운 구독인 경우 플랜 가져오기
            $plan = Plan::findOrFail($planId);
            if ($plan->type !== 'server') {
                return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                    ->with('error', '서버 용량 플랜이 아닙니다.');
            }
        } else {
            // 기존 구독(플랜 변경)인 경우
            $subscriptionId = session('server_capacity_subscription_id');
            if (!$subscriptionId) {
                return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                    ->with('error', '서버 용량 플랜 정보를 찾을 수 없습니다.');
            }

            $subscription = Subscription::with('plan')->findOrFail($subscriptionId);
            $plan = $subscription->plan;

            if (!$plan || $plan->type !== 'server') {
                return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
                    ->with('error', '서버 용량 플랜이 아닙니다.');
            }
        }

        // 대상 사이트 가져오기
        $targetSite = Site::findOrFail($request->target_site_id);

        // 사용자가 소유한 사이트인지 확인
        if ($targetSite->created_by !== $user->id) {
            return redirect()->route('payment.select-server-capacity-site', ['site' => $masterSite->slug])
                ->with('error', '이 사이트를 변경할 권한이 없습니다.');
        }

        // 새로운 구독인 경우 구독 생성 및 결제 진행
        if ($isNewSubscription) {
            // 구독 생성
            $subscription = $this->subscriptionService->createSubscription(
                $targetSite,
                $plan,
                0, // trial 제거
                $user
            );

            // 결제 기록 생성
            $orderId = 'order_' . Str::random(16);
            $planPrice = $plan->price ?? 0;

            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'site_id' => $targetSite->id,
                'toss_order_id' => $orderId,
                'amount' => $planPrice,
                'status' => 'pending',
                'payment_type' => 'subscription',
            ]);

            // 세션에 저장
            session([
                'server_capacity_subscription_id' => $subscription->id,
                'server_capacity_payment_id' => $payment->id,
                'server_capacity_target_site_id' => $targetSite->id,
            ]);

            // 결제 페이지로 이동
            return redirect()->route('payment.checkout', [
                'plan' => $plan->slug,
                'order_id' => $orderId,
                'subscription_id' => $subscription->id,
            ]);
        }

        // 기존 구독(플랜 변경)인 경우 서버 용량 적용
        $storageLimit = $plan->limits['storage'] ?? 0;
        $trafficLimit = $plan->traffic_limit_mb ?? 0;

        // 기존 용량에 추가 (서버 용량 플랜은 추가 용량)
        $currentStorageLimit = $targetSite->storage_limit_mb ?? 0;
        $currentTrafficLimit = $targetSite->traffic_limit_mb ?? 0;

        $targetSite->update([
            'storage_limit_mb' => $currentStorageLimit + $storageLimit,
            'traffic_limit_mb' => $currentTrafficLimit + $trafficLimit,
        ]);

        // 구독을 대상 사이트에 연결
        $subscription->update([
            'site_id' => $targetSite->id,
        ]);

        // 세션 정리
        $isPlanChange = session('server_capacity_is_plan_change', false);
        session()->forget([
            'server_capacity_subscription_id',
            'server_capacity_payment_id',
            'server_capacity_is_plan_change',
        ]);

        $message = $isPlanChange 
            ? '플랜 변경이 완료되었고 서버 용량이 적용되었습니다.'
            : '서버 용량이 적용되었습니다.';

        return redirect()->route('users.my-sites', ['site' => $masterSite->slug])
            ->with('success', $message);
    }

}

