<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\SubscriptionService;
use App\Services\TossPaymentService;
use App\Mail\PaymentFailedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RetryFailedPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed payments for past due subscriptions';

    protected $subscriptionService;
    protected $tossPaymentService;

    /**
     * Create a new command instance.
     */
    public function __construct(SubscriptionService $subscriptionService, TossPaymentService $tossPaymentService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
        $this->tossPaymentService = $tossPaymentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('결제 실패 재시도를 시작합니다...');

        $today = Carbon::now()->startOfDay();
        
        // 결제가 필요한 구독 찾기 (past_due 상태이거나 결제 기간이 지난 경우)
        $subscriptions = Subscription::whereIn('status', ['past_due', 'active', 'trial'])
            ->where(function ($query) use ($today) {
                // 결제 기간이 지난 경우
                $query->where(function ($q) use ($today) {
                    $q->whereNotNull('trial_ends_at')
                      ->whereDate('trial_ends_at', '<=', $today)
                      ->where('status', 'trial');
                })->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('current_period_end')
                      ->whereDate('current_period_end', '<=', $today)
                      ->where('status', 'active');
                })->orWhere('status', 'past_due');
            })
            ->with(['site', 'plan'])
            ->get();

        $successCount = 0;
        $failCount = 0;
        $suspendedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // 빌링 키가 없으면 재결제 불가
                if (!$subscription->toss_billing_key) {
                    $this->warn("구독 ID {$subscription->id}: 빌링 키가 없어 재결제할 수 없습니다.");
                    continue;
                }

                // 마지막 결제 실패일이 어제인 경우에만 재시도 (다음날 재시도)
                if ($subscription->last_payment_failed_at) {
                    $lastFailedDate = Carbon::parse($subscription->last_payment_failed_at)->startOfDay();
                    $yesterday = $today->copy()->subDay();
                    
                    // 마지막 실패일이 어제가 아니면 스킵
                    if (!$lastFailedDate->equalTo($yesterday)) {
                        continue;
                    }
                }

                // 3일 연속 실패한 경우 서비스 일시 중지
                if ($subscription->retry_count >= 3) {
                    $this->subscriptionService->suspendSubscription($subscription);
                    $this->sendFailureNotification($subscription, null);
                    $suspendedCount++;
                    $this->warn("구독 ID {$subscription->id}: 3일 연속 실패로 서비스 일시 중지");
                    continue;
                }

                // 재결제 시도
                $result = $this->retryPayment($subscription);

                if ($result['success']) {
                    // 결제 성공
                    $this->subscriptionService->activateSubscription($subscription);
                    $successCount++;
                    $this->info("✓ 구독 ID {$subscription->id}: 재결제 성공");
                } else {
                    // 결제 실패
                    $this->subscriptionService->markAsPastDue($subscription);
                    $this->sendFailureNotification($subscription, $result['payment']);
                    $failCount++;
                    $this->error("✗ 구독 ID {$subscription->id}: 재결제 실패 - " . ($result['error'] ?? '알 수 없는 오류'));
                }
            } catch (\Exception $e) {
                $this->error("✗ 구독 ID {$subscription->id}: 오류 발생 - " . $e->getMessage());
                Log::error('Payment retry failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failCount++;
            }
        }

        $this->info("재결제 완료: 성공 {$successCount}건, 실패 {$failCount}건, 일시 중지 {$suspendedCount}건");
        return 0;
    }

    /**
     * Retry payment for a subscription.
     */
    protected function retryPayment(Subscription $subscription)
    {
        try {
            $plan = $subscription->plan;
            $amount = $plan->price;
            $orderId = 'retry_' . $subscription->id . '_' . Str::random(10);

            // 토스 페이먼츠 정기결제 처리
            $response = $this->tossPaymentService->processRecurringPayment(
                $subscription,
                $subscription->toss_billing_key,
                $amount,
                $orderId
            );

            // 결제 기록 생성
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'site_id' => $subscription->site_id,
                'toss_payment_key' => $response['paymentKey'] ?? null,
                'toss_order_id' => $orderId,
                'amount' => $amount,
                'status' => 'paid',
                'payment_type' => 'retry',
                'paid_at' => Carbon::now(),
                'toss_response' => $response,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
            ];
        } catch (\Exception $e) {
            // 결제 실패 기록 생성
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'site_id' => $subscription->site_id,
                'toss_order_id' => 'retry_' . $subscription->id . '_' . Str::random(10),
                'amount' => $subscription->plan->price,
                'status' => 'failed',
                'payment_type' => 'retry',
                'failed_at' => Carbon::now(),
                'failure_reason' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'payment' => $payment,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send payment failure notification.
     */
    protected function sendFailureNotification(Subscription $subscription, Payment $payment = null)
    {
        try {
            // 이미 오늘 알림을 보냈는지 확인
            $today = Carbon::now()->startOfDay();
            if ($subscription->failure_notification_sent_at && 
                Carbon::parse($subscription->failure_notification_sent_at)->startOfDay()->equalTo($today)) {
                return; // 이미 오늘 알림을 보냄
            }

            // 사이트의 관리자 이메일 찾기
            $adminUser = $this->getAdminUser($subscription->site);
            
            if (!$adminUser || !$adminUser->email) {
                $this->warn("구독 ID {$subscription->id}: 관리자 이메일을 찾을 수 없습니다.");
                return;
            }

            // 알림 메일 전송
            Mail::to($adminUser->email)->send(new PaymentFailedMail($subscription, $payment));

            // 알림 전송 기록 업데이트
            $subscription->update([
                'failure_notification_sent_at' => $today,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment failure notification send failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get admin user for a site.
     */
    protected function getAdminUser($site)
    {
        // 사이트의 관리자 찾기
        $adminUser = \App\Models\User::where('site_id', $site->id)
            ->where('role', 'admin')
            ->first();

        // 관리자가 없으면 첫 번째 사용자
        if (!$adminUser) {
            $adminUser = \App\Models\User::where('site_id', $site->id)
                ->first();
        }

        return $adminUser;
    }
}



