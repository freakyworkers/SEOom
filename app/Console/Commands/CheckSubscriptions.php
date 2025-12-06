<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Mail\PaymentReminderMail;
use App\Mail\PaymentFailedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscriptions and send payment reminders';

    protected $subscriptionService;

    /**
     * Create a new command instance.
     */
    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('구독 확인 및 알림 전송을 시작합니다...');

        // 7일전 알림 전송
        $this->sendReminders(7);
        
        // 3일전 알림 전송
        $this->sendReminders(3);
        
        // 1일전 알림 전송
        $this->sendReminders(1);

        $this->info('구독 확인 및 알림 전송이 완료되었습니다.');
        return 0;
    }

    /**
     * Send payment reminders for subscriptions.
     */
    protected function sendReminders(int $daysBefore)
    {
        $today = Carbon::now()->startOfDay();
        $reminderDate = $today->copy()->addDays($daysBefore);
        
        // 필드명 매핑
        $fieldMap = [
            7 => 'reminder_sent_7days',
            3 => 'reminder_sent_3days',
            1 => 'reminder_sent_1day',
        ];
        $fieldName = $fieldMap[$daysBefore] ?? "reminder_sent_{$daysBefore}days";

        // 결제 예정일이 N일 후인 구독 찾기
        $subscriptions = Subscription::whereIn('status', ['trial', 'active'])
            ->where(function ($query) use ($reminderDate) {
                $query->where(function ($q) use ($reminderDate) {
                    // 트라이얼 종료일이 N일 후
                    $q->whereNotNull('trial_ends_at')
                      ->whereDate('trial_ends_at', $reminderDate)
                      ->where('status', 'trial');
                })->orWhere(function ($q) use ($reminderDate) {
                    // 결제 기간 종료일이 N일 후
                    $q->whereNotNull('current_period_end')
                      ->whereDate('current_period_end', $reminderDate)
                      ->where('status', 'active');
                });
            })
            ->where(function ($query) use ($fieldName) {
                // 해당 알림을 아직 보내지 않은 경우
                $query->whereNull($fieldName)
                      ->orWhere($fieldName, '<', $today);
            })
            ->with(['site', 'plan'])
            ->get();

        $count = 0;
        foreach ($subscriptions as $subscription) {
            try {
                // 사이트의 관리자 이메일 찾기
                $adminUser = $this->getAdminUser($subscription->site);
                
                if (!$adminUser || !$adminUser->email) {
                    $this->warn("구독 ID {$subscription->id}: 관리자 이메일을 찾을 수 없습니다.");
                    continue;
                }

                // 알림 메일 전송
                Mail::to($adminUser->email)->send(new PaymentReminderMail($subscription, $daysBefore));

                // 알림 전송 기록 업데이트
                $subscription->update([
                    $fieldName => $today,
                ]);

                $count++;
                $this->info("✓ 구독 ID {$subscription->id}: {$daysBefore}일전 알림 전송 완료");
            } catch (\Exception $e) {
                $this->error("✗ 구독 ID {$subscription->id}: 알림 전송 실패 - " . $e->getMessage());
                Log::error('Payment reminder send failed', [
                    'subscription_id' => $subscription->id,
                    'days_before' => $daysBefore,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($count > 0) {
            $this->info("{$daysBefore}일전 알림: {$count}건 전송 완료");
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

