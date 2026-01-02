<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use Illuminate\Support\Facades\Artisan;

class BackupAllSites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:all-sites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create backups for all sites individually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('모든 사이트 백업을 시작합니다...');

        // 마스터 사이트를 제외한 모든 사이트 가져오기
        $sites = Site::where('is_master_site', false)->get();

        if ($sites->isEmpty()) {
            $this->warn('백업할 사이트가 없습니다.');
            return 0;
        }

        $this->info("총 {$sites->count()}개의 사이트를 백업합니다.");

        $successCount = 0;
        $failCount = 0;

        foreach ($sites as $site) {
            $this->info("사이트 백업 중: {$site->name} ({$site->slug})");
            
            try {
                $exitCode = Artisan::call('backup:site', [
                    'site_id' => $site->id,
                ]);

                if ($exitCode === 0) {
                    $this->info("✓ {$site->name} 백업 완료");
                    $successCount++;
                } else {
                    $this->error("✗ {$site->name} 백업 실패");
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->error("✗ {$site->name} 백업 중 오류: " . $e->getMessage());
                $failCount++;
            }
        }

        $this->info("\n백업 완료!");
        $this->info("성공: {$successCount}개, 실패: {$failCount}개");

        return $failCount > 0 ? 1 : 0;
    }
}

