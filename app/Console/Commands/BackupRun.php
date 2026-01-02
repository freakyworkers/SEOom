<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full database backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('백업을 시작합니다...');

        try {
            $databaseName = DB::connection()->getDatabaseName();
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$databaseName}_{$timestamp}.sql";
            $backupPath = storage_path('app/backups');
            
            // 백업 디렉토리 생성
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filepath = $backupPath . '/' . $filename;

            // MySQL 덤프 실행
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbName = $databaseName;

            // mysqldump 명령어 구성
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            // 비밀번호를 환경변수로 전달 (보안)
            putenv("MYSQL_PWD={$dbPass}");
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s %s > %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                $error = implode("\n", $output);
                $this->error("백업 실패: {$error}");
                return 1;
            }

            // 파일 크기 확인
            $fileSize = filesize($filepath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            $this->info("백업이 완료되었습니다: {$filename} ({$fileSizeMB} MB)");
            
            // Storage에 저장 (web에서 접근 가능하도록)
            Storage::disk('local')->put("backups/{$filename}", file_get_contents($filepath));

            return 0;
        } catch (\Exception $e) {
            $this->error("백업 중 오류 발생: " . $e->getMessage());
            return 1;
        }
    }
}

