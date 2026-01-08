<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\SubscriptionService;
use App\Services\TossPaymentService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProcessRecurringPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring payments for active subscriptions';

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
        $this->info('정기 결제 처리를 시작합니다...');

        $today = Carbon::now()->startOfDay();
        
        // 결제 기간이 지난 활성 구독 찾기
        $subscriptions = Subscription::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->whereDate('current_period_end', '<=', $today)
            ->whereNotNull('toss_billing_key') // 빌링 키가 있어야 정기 결제 가능
            ->with(['site', 'plan', 'user'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('결제가 필요한 구독이 없습니다.');
            return 0;
        }

        $this->info("결제가 필요한 구독: {$subscriptions->count()}개");

        $successCount = 0;
        $failCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->info("구독 ID {$subscription->id} 처리 중...");

                // 정기 결제 실행
                $result = $this->processPayment($subscription);

                if ($result['success']) {
                    // 결제 성공
                    $this->subscriptionService->activateSubscription($subscription);
                    $successCount++;
                    $this->info("✓ 구독 ID {$subscription->id}: 정기 결제 성공");
                } else {
                    // 결제 실패
                    $this->subscriptionService->markAsPastDue($subscription);
                    $failCount++;
                    $this->error("✗ 구독 ID {$subscription->id}: 정기 결제 실패 - " . ($result['error'] ?? '알 수 없는 오류'));
                }
            } catch (\Exception $e) {
                $this->error("✗ 구독 ID {$subscription->id}: 오류 발생 - " . $e->getMessage());
                Log::error('Recurring payment processing failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failCount++;
            }
        }

        $this->info("정기 결제 완료: 성공 {$successCount}건, 실패 {$failCount}건");
        return 0;
    }

    /**
     * Process recurring payment for a subscription.
     */
    protected function processPayment(Subscription $subscription)
    {
        try {
            $plan = $subscription->plan;
            $amount = (int) $plan->price; // 원 단위
            $orderId = 'recurring_' . $subscription->id . '_' . Carbon::now()->format('Ymd') . '_' . Str::random(8);

            // customerKey 생성 (토스페이먼츠 가이드에 따르면 고유한 값이어야 함)
            $customerKey = 'customer_' . $subscription->user_id;

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
                'amount' => $plan->price,
                'status' => 'paid',
                'payment_type' => 'recurring',
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
                'toss_order_id' => 'recurring_' . $subscription->id . '_' . Carbon::now()->format('Ymd') . '_' . Str::random(8),
                'amount' => $subscription->plan->price,
                'status' => 'failed',
                'payment_type' => 'recurring',
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
}







