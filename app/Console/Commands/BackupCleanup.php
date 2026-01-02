<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup {--days=7 : Number of days to keep backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete backup files older than specified days (default: 7 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("{$days}일 이상 된 백업 파일을 삭제합니다...");

        try {
            $backupPath = storage_path('app/backups');
            
            if (!file_exists($backupPath)) {
                $this->info("백업 디렉토리가 없습니다.");
                return 0;
            }

            $files = Storage::files('backups');
            $deletedCount = 0;
            $deletedSize = 0;

            foreach ($files as $file) {
                $lastModified = Storage::lastModified($file);
                $fileDate = Carbon::createFromTimestamp($lastModified);

                if ($fileDate->lt($cutoffDate)) {
                    $fileSize = Storage::size($file);
                    Storage::delete($file);
                    
                    // 로컬 파일 시스템에서도 삭제
                    $localPath = storage_path('app/' . $file);
                    if (file_exists($localPath)) {
                        unlink($localPath);
                    }
                    
                    $deletedCount++;
                    $deletedSize += $fileSize;
                    $this->line("삭제: " . basename($file) . " ({$fileDate->format('Y-m-d H:i:s')})");
                }
            }

            if ($deletedCount > 0) {
                $deletedSizeMB = round($deletedSize / 1024 / 1024, 2);
                $this->info("총 {$deletedCount}개의 백업 파일이 삭제되었습니다. (총 {$deletedSizeMB} MB)");
            } else {
                $this->info("삭제할 백업 파일이 없습니다.");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("백업 정리 중 오류 발생: " . $e->getMessage());
            return 1;
        }
    }
}

