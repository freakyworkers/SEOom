<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Site;
use Carbon\Carbon;

class BackupSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:site {site_id : The ID of the site to backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup for a specific site';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siteId = $this->argument('site_id');
        $site = Site::find($siteId);

        if (!$site) {
            $this->error("사이트를 찾을 수 없습니다: {$siteId}");
            return 1;
        }

        $this->info("사이트 백업을 시작합니다: {$site->name} ({$site->slug})");

        try {
            $databaseName = DB::connection()->getDatabaseName();
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_site_{$site->slug}_{$timestamp}.sql";
            $backupPath = storage_path('app/backups');
            
            // 백업 디렉토리 생성
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filepath = $backupPath . '/' . $filename;

            // MySQL 덤프 실행 (특정 사이트 관련 테이블만)
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbName = $databaseName;

            // 사이트 관련 테이블 목록
            $tables = [
                'sites',
                'users',
                'boards',
                'posts',
                'comments',
                'site_settings',
                'main_widget_containers',
                'main_widgets',
                'custom_pages',
                'custom_page_widget_containers',
                'custom_page_widgets',
                'sidebar_widgets',
                'menus',
                'mobile_menus',
                'banners',
                'popups',
                'contact_forms',
                'contact_form_submissions',
                'maps',
                'notifications',
                'messages',
                'chat_messages',
                'chat_settings',
                'chat_guest_sessions',
                'reports',
                'penalties',
                'blocked_users',
                'post_attachments',
                'post_likes',
                'saved_posts',
                'attendances',
                'attendance_settings',
                'visitors',
                'topics',
                'post_topic',
                'event_options',
                'event_participants',
                'user_ranks',
                'custom_codes',
                'email_verifications',
                'phone_verifications',
                'point_exchange_settings',
                'point_exchange_products',
                'point_exchange_applications',
                'event_application_settings',
                'event_application_products',
                'event_application_submissions',
                'toggle_menus',
                'toggle_menu_items',
            ];

            // PHP로 직접 데이터 추출 (사이트별 백업)
            $this->info("데이터를 추출하는 중...");
            
            $sql = "-- Site Backup: {$site->name} ({$site->slug})\n";
            $sql .= "-- Created: " . Carbon::now()->toDateTimeString() . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    continue;
                }

                // site_id 컬럼이 있는지 확인
                $hasSiteId = DB::getSchemaBuilder()->hasColumn($table, 'site_id');
                
                if ($hasSiteId) {
                    $sql .= "-- Table: {$table}\n";
                    $sql .= "DELETE FROM `{$table}` WHERE site_id = {$siteId};\n\n";
                    
                    $rows = DB::table($table)->where('site_id', $siteId)->get();
                    
                    if ($rows->count() > 0) {
                        foreach ($rows as $row) {
                            $rowArray = (array)$row;
                            $values = [];
                            $columns = [];
                            
                            foreach ($rowArray as $key => $value) {
                                $columns[] = "`{$key}`";
                                if ($value === null) {
                                    $values[] = 'NULL';
                                } elseif (is_numeric($value)) {
                                    $values[] = $value;
                                } else {
                                    $values[] = "'" . addslashes($value) . "'";
                                }
                            }
                            
                            $columnsStr = implode(', ', $columns);
                            $valuesStr = implode(', ', $values);
                            $sql .= "INSERT INTO `{$table}` ({$columnsStr}) VALUES ({$valuesStr});\n";
                        }
                        $sql .= "\n";
                    }
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            file_put_contents($filepath, $sql);

            if (!file_exists($filepath) || filesize($filepath) === 0) {
                $this->error("백업 파일 생성 실패");
                return 1;
            }

            // 파일 크기 확인
            $fileSize = filesize($filepath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            $this->info("백업이 완료되었습니다: {$filename} ({$fileSizeMB} MB)");
            
            // Storage에 저장
            Storage::disk('local')->put("backups/{$filename}", file_get_contents($filepath));

            return 0;
        } catch (\Exception $e) {
            $this->error("백업 중 오류 발생: " . $e->getMessage());
            return 1;
        }
    }
}

