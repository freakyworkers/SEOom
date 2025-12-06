<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 1시간마다 활성화된 크롤러 실행
        $schedule->command('crawlers:run')->hourly();
        
        // 매일 자정에 구독 확인 및 알림 전송
        $schedule->command('subscriptions:check')->daily();
        
        // 매일 오전 9시에 정기 결제 처리
        $schedule->command('payments:process-recurring')->dailyAt('09:00');
        
        // 매일 오전 10시에 결제 실패 재시도
        $schedule->command('payments:retry')->dailyAt('10:00');
        
        // 매시간 24시간 이상 된 채팅 메시지 삭제 (auto_delete_24h 활성화된 사이트만)
        $schedule->command('chat:delete-old-messages')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}






