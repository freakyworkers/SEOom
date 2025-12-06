<?php

/**
 * 로컬의 특정 사이트 데이터를 서버로 이전하기 위한 Export 스크립트
 * 
 * 사용 방법:
 * 1. 로컬에서 실행: php export_sites_to_server.php
 * 2. 생성된 sites_export.sql 파일을 서버로 복사
 * 3. 서버에서 실행: mysql -u seoom_user -p seoom < sites_export.sql
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Export할 사이트 slug 목록
$siteSlugs = ['test-site', 'e'];

echo "🚀 사이트 데이터 Export 시작...\n\n";

// 1. 사이트 정보 확인
$sites = DB::table('sites')
    ->whereIn('slug', $siteSlugs)
    ->whereNull('deleted_at')
    ->get();

if ($sites->isEmpty()) {
    echo "❌ 해당 사이트를 찾을 수 없습니다.\n";
    echo "찾는 사이트: " . implode(', ', $siteSlugs) . "\n";
    exit(1);
}

$siteIds = $sites->pluck('id')->toArray();
echo "✅ 찾은 사이트:\n";
foreach ($sites as $site) {
    echo "   - {$site->name} (slug: {$site->slug}, id: {$site->id})\n";
}
echo "\n";

// 2. 관련 테이블 목록 (site_id 또는 siteId 컬럼이 있는 테이블들)
$relatedTables = [
    'sites' => 'id',
    'users' => 'site_id',
    'boards' => 'site_id',
    'posts' => 'site_id',
    'comments' => 'site_id',
    'site_settings' => 'site_id',
    'subscriptions' => 'site_id',
    'payments' => 'site_id',
    'notifications' => 'site_id',
    'messages' => 'site_id',
    'saved_posts' => 'site_id',
    'post_likes' => 'site_id',
    'post_attachments' => 'site_id',
    'topics' => 'site_id',
    'post_topic' => 'site_id',
    'visitors' => 'site_id',
    'attendances' => 'site_id',
    'attendance_settings' => 'site_id',
    'point_exchange_settings' => 'site_id',
    'point_exchange_products' => 'site_id',
    'point_exchange_applications' => 'site_id',
    'event_application_settings' => 'site_id',
    'event_application_products' => 'site_id',
    'event_application_submissions' => 'site_id',
    'menus' => 'site_id',
    'mobile_menus' => 'site_id',
    'banners' => 'site_id',
    'popups' => 'site_id',
    'user_ranks' => 'site_id',
    'custom_codes' => 'site_id',
    'email_verifications' => 'site_id',
    'phone_verifications' => 'site_id',
    'sidebar_widgets' => 'site_id',
    'main_widget_containers' => 'site_id',
    'main_widgets' => 'site_id',
    'custom_pages' => 'site_id',
    'contact_forms' => 'site_id',
    'contact_form_submissions' => 'site_id',
    'maps' => 'site_id',
    'crawlers' => 'site_id',
    'toggle_menus' => 'site_id',
    'chat_settings' => 'site_id',
    'chat_messages' => 'site_id',
    'chat_guest_sessions' => 'site_id',
    'reports' => 'site_id',
    'penalties' => 'site_id',
    'blocked_users' => 'site_id',
    'user_addons' => 'site_id',
];

// 3. SQL 파일 생성
$sql = "-- 사이트 데이터 Export\n";
$sql .= "-- 생성일: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- 사이트: " . implode(', ', $siteSlugs) . "\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

// 4. 각 테이블의 데이터 Export
foreach ($relatedTables as $tableName => $siteColumn) {
    if (!Schema::hasTable($tableName)) {
        echo "⚠️  테이블 '{$tableName}'이 존재하지 않습니다. 건너뜁니다.\n";
        continue;
    }
    
    if (!Schema::hasColumn($tableName, $siteColumn)) {
        // sites 테이블은 id로 조회
        if ($tableName === 'sites') {
            $query = DB::table($tableName)
                ->whereIn('id', $siteIds)
                ->whereNull('deleted_at');
        } else {
            echo "⚠️  테이블 '{$tableName}'에 '{$siteColumn}' 컬럼이 없습니다. 건너뜁니다.\n";
            continue;
        }
    } else {
        $query = DB::table($tableName)
            ->whereIn($siteColumn, $siteIds);
        
        // soft deletes가 있는 경우
        if (Schema::hasColumn($tableName, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
    }
    
    $records = $query->get();
    
    if ($records->isEmpty()) {
        echo "ℹ️  테이블 '{$tableName}': 데이터 없음\n";
        continue;
    }
    
    echo "📦 테이블 '{$tableName}': {$records->count()}개 레코드\n";
    
    // 컬럼 목록 가져오기
    $columns = Schema::getColumnListing($tableName);
    
    $sql .= "-- {$tableName} 테이블 데이터\n";
    $sql .= "DELETE FROM `{$tableName}` WHERE ";
    if ($tableName === 'sites') {
        $sql .= "`id` IN (" . implode(',', $siteIds) . ");\n";
    } else {
        $sql .= "`{$siteColumn}` IN (" . implode(',', $siteIds) . ");\n";
    }
    
    foreach ($records as $record) {
        $values = [];
        foreach ($columns as $column) {
            $value = $record->$column ?? null;
            if ($value === null) {
                $values[] = 'NULL';
            } elseif (is_numeric($value)) {
                $values[] = $value;
            } elseif (is_bool($value)) {
                $values[] = $value ? 1 : 0;
            } else {
                $values[] = DB::connection()->getPdo()->quote($value);
            }
        }
        
        $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
    }
    
    $sql .= "\n";
}

// users 테이블의 경우 site_id 외에도 posts, comments 등에서 참조될 수 있으므로
// 해당 사이트의 게시글/댓글 작성자도 포함해야 할 수 있습니다.
// 하지만 일단 site_id로 필터링한 사용자만 포함합니다.

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

// 5. 파일 저장
$filename = 'sites_export.sql';
file_put_contents($filename, $sql);

echo "\n✅ Export 완료!\n";
echo "📁 파일: {$filename}\n";
echo "📊 총 " . count($sites) . "개 사이트의 데이터가 포함되었습니다.\n";
echo "\n다음 단계:\n";
echo "1. {$filename} 파일을 서버로 복사하세요.\n";
echo "2. 서버에서 다음 명령어를 실행하세요:\n";
echo "   mysql -u seoom_user -p seoom < sites_export.sql\n";

