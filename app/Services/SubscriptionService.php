<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription for a site.
     */
    public function createSubscription(Site $site, Plan $plan, int $trialDays = 0, ?User $user = null): Subscription
    {
        $now = Carbon::now();
        
        // 플랜 결제 타입에 따라 기간 설정
        if ($plan->billing_type === 'one_time') {
            // 1회성 결제는 무제한 (또는 매우 긴 기간)
            $periodEnd = $now->copy()->addYears(100); // 사실상 무제한
        } elseif ($plan->billing_type === 'monthly') {
            // 월간 결제는 1개월 후
            $periodEnd = $now->copy()->addMonth();
        } else {
            // 무료 플랜은 무제한
            $periodEnd = $now->copy()->addYears(100);
        }

        $subscription = Subscription::create([
            'site_id' => $site->id,
            'user_id' => $user ? $user->id : null,
            'plan_id' => $plan->id,
            'status' => 'active', // 바로 active로 생성
            'trial_ends_at' => null, // trial 제거
            'current_period_start' => $now,
            'current_period_end' => $periodEnd,
            'retry_count' => 0,
        ]);

        return $subscription;
    }

    /**
     * Activate subscription after successful payment.
     */
    public function activateSubscription(Subscription $subscription): void
    {
        $plan = $subscription->plan;
        $now = Carbon::now();
        
        // 플랜 결제 타입에 따라 기간 설정
        if ($plan && $plan->billing_type === 'one_time') {
            // 1회성 결제는 무제한 (또는 매우 긴 기간)
            $nextPeriodEnd = $now->copy()->addYears(100); // 사실상 무제한
        } else {
            // 월간 결제는 1개월 후
            $nextPeriodEnd = $now->copy()->addMonth();
        }

        $subscription->update([
            'status' => 'active',
            'current_period_start' => $now,
            'current_period_end' => $nextPeriodEnd,
            'retry_count' => 0,
            'last_payment_failed_at' => null,
        ]);

        // Update site status to active if it was suspended
        if ($subscription->site->status === 'suspended') {
            $subscription->site->update(['status' => 'active']);
        }
    }

    /**
     * Mark subscription as past due.
     */
    public function markAsPastDue(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'past_due',
            'last_payment_failed_at' => Carbon::now(),
            'retry_count' => $subscription->retry_count + 1,
        ]);
    }

    /**
     * Suspend subscription after multiple failed payments.
     */
    public function suspendSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'suspended',
        ]);

        // Suspend the site
        $subscription->site->update(['status' => 'suspended']);
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => Carbon::now(),
        ]);
    }

    /**
     * Get trial days from master settings.
     */
    public function getTrialDays(): int
    {
        $masterSite = \App\Models\Site::getMasterSite();
        if (!$masterSite) {
            return 7; // Default
        }

        $trialDays = $masterSite->getSetting('subscription_trial_days', 7);
        return (int) $trialDays;
    }

    /**
     * Check if subscription needs payment.
     */
    public function needsPayment(Subscription $subscription): bool
    {
        // If active, check if current period has ended
        if ($subscription->isActive()) {
            return $subscription->current_period_end && $subscription->current_period_end->isPast();
        }

        // If past due, always needs payment
        if ($subscription->isPastDue()) {
            return true;
        }

        return false;
    }

    /**
     * Get subscriptions that need payment.
     */
    public function getSubscriptionsNeedingPayment(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::whereIn('status', ['active', 'past_due'])
            ->where('current_period_end', '<=', Carbon::now())
            ->with(['site', 'plan'])
            ->get();
    }

    /**
     * Get subscriptions that need payment reminders.
     */
    public function getSubscriptionsNeedingReminder(int $daysBefore = 7): \Illuminate\Database\Eloquent\Collection
    {
        $reminderDate = Carbon::now()->addDays($daysBefore);

        return Subscription::where('status', 'active')
            ->where('current_period_end', '<=', $reminderDate)
            ->where('current_period_end', '>', Carbon::now())
            ->with(['site', 'plan'])
            ->get();
    }

    /**
     * Change subscription plan.
     * 
     * @param Subscription $subscription
     * @param Plan $newPlan
     * @param bool $immediate Whether to change immediately or at next billing cycle
     * @return array ['success' => bool, 'amount' => float, 'message' => string]
     */
    public function changePlan(Subscription $subscription, Plan $newPlan, bool $immediate = false): array
    {
        $currentPlan = $subscription->plan;
        
        // 같은 플랜이면 변경 불가
        if ($currentPlan->id === $newPlan->id) {
            return [
                'success' => false,
                'amount' => 0,
                'message' => '이미 동일한 플랜입니다.',
            ];
        }

        $currentPrice = (float) $currentPlan->price;
        $newPrice = (float) $newPlan->price;
        $priceDifference = $newPrice - $currentPrice;

        // 즉시 변경인 경우
        if ($immediate) {
            // 남은 기간 계산 (일 단위)
            $now = Carbon::now();
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

            // 플랜 변경
            $subscription->update([
                'plan_id' => $newPlan->id,
            ]);

            // 사이트 플랜도 업데이트
            $subscription->site->update([
                'plan' => $newPlan->slug,
            ]);

            return [
                'success' => true,
                'amount' => $proratedAmount,
                'message' => $proratedAmount > 0 
                    ? '플랜이 상향 조정되었습니다. 차액 결제가 필요합니다.' 
                    : ($proratedAmount < 0 
                        ? '플랜이 하향 조정되었습니다. 차액이 환불됩니다.' 
                        : '플랜이 변경되었습니다.'),
            ];
        } else {
            // 다음 결제일에 변경
            // 플랜 변경 예약 (구독에 저장하거나 별도 테이블에 저장)
            // 현재는 간단하게 플랜만 변경하고 다음 결제일에 새 플랜으로 결제되도록 함
            $subscription->update([
                'plan_id' => $newPlan->id,
            ]);

            return [
                'success' => true,
                'amount' => $priceDifference,
                'message' => '다음 결제일(' . ($subscription->current_period_end ? $subscription->current_period_end->format('Y-m-d') : '-') . ')부터 새 플랜이 적용됩니다.',
            ];
        }
    }
}

