<?php

/**
 * 로컬의 plans 테이블 데이터를 서버로 동기화하는 스크립트
 * 
 * 사용 방법:
 * 1. 로컬에서 실행: php sync_plans_to_server.php export
 * 2. 생성된 plans_export.sql 파일을 서버로 복사
 * 3. 서버에서 실행: php sync_plans_to_server.php import
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$command = $argv[1] ?? 'help';

if ($command === 'export') {
    exportPlans();
} elseif ($command === 'import') {
    importPlans();
} else {
    showHelp();
}

function exportPlans()
{
    if (!Schema::hasTable('plans')) {
        echo "❌ plans 테이블이 존재하지 않습니다.\n";
        exit(1);
    }

    $plans = DB::table('plans')->get();
    
    if ($plans->isEmpty()) {
        echo "⚠️  plans 테이블에 데이터가 없습니다.\n";
        exit(0);
    }

    $sql = "-- Plans 테이블 데이터 Export\n";
    $sql .= "-- 생성일: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 기존 데이터 삭제 (선택사항 - 주석 처리)
    // $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
    // $sql .= "TRUNCATE TABLE plans;\n";
    // $sql .= "SET FOREIGN_KEY_CHECKS=1;\n\n";
    
    $sql .= "-- 기존 데이터 삭제 (중복 방지)\n";
    $sql .= "DELETE FROM plans;\n\n";
    
    $sql .= "-- Plans 데이터 삽입\n";
    
    foreach ($plans as $plan) {
        $sql .= "INSERT INTO plans (";
        $sql .= "id, name, slug, description, type, billing_type, ";
        $sql .= "price, one_time_price, traffic_limit_mb, ";
        $sql .= "features, limits, sort_order, is_active, is_default, ";
        $sql .= "created_at, updated_at, deleted_at";
        $sql .= ") VALUES (";
        
        $sql .= $plan->id . ", ";
        $sql .= "'" . addslashes($plan->name) . "', ";
        $sql .= "'" . addslashes($plan->slug) . "', ";
        $sql .= ($plan->description ? "'" . addslashes($plan->description) . "'" : "NULL") . ", ";
        $sql .= "'" . addslashes($plan->type ?? 'landing') . "', ";
        $sql .= "'" . addslashes($plan->billing_type ?? 'free') . "', ";
        $sql .= ($plan->price ?? 0) . ", ";
        $sql .= ($plan->one_time_price ? $plan->one_time_price : "NULL") . ", ";
        $sql .= ($plan->traffic_limit_mb ? $plan->traffic_limit_mb : "NULL") . ", ";
        $sql .= "'" . addslashes($plan->features ?? '{}') . "', ";
        $sql .= "'" . addslashes($plan->limits ?? '{}') . "', ";
        $sql .= ($plan->sort_order ?? 0) . ", ";
        $sql .= ($plan->is_active ? 1 : 0) . ", ";
        $sql .= ($plan->is_default ? 1 : 0) . ", ";
        $sql .= "'" . ($plan->created_at ?? date('Y-m-d H:i:s')) . "', ";
        $sql .= "'" . ($plan->updated_at ?? date('Y-m-d H:i:s')) . "', ";
        $sql .= ($plan->deleted_at ? "'" . $plan->deleted_at . "'" : "NULL");
        $sql .= ");\n";
    }
    
    $filename = 'plans_export.sql';
    file_put_contents($filename, $sql);
    
    echo "✅ Plans 데이터를 {$filename} 파일로 export했습니다.\n";
    echo "📊 총 {$plans->count()}개의 플랜이 export되었습니다.\n";
    echo "\n";
    echo "다음 단계:\n";
    echo "1. {$filename} 파일을 서버로 복사하세요.\n";
    echo "2. 서버에서 다음 명령어를 실행하세요:\n";
    echo "   php sync_plans_to_server.php import\n";
}

function importPlans()
{
    $filename = 'plans_export.sql';
    
    if (!file_exists($filename)) {
        echo "❌ {$filename} 파일을 찾을 수 없습니다.\n";
        echo "먼저 로컬에서 export 명령어를 실행하세요.\n";
        exit(1);
    }
    
    if (!Schema::hasTable('plans')) {
        echo "❌ plans 테이블이 존재하지 않습니다.\n";
        exit(1);
    }
    
    $sql = file_get_contents($filename);
    
    // 외래 키 제약 조건 비활성화
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    try {
        // SQL 문을 세미콜론으로 분리하여 실행
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                DB::statement($statement);
            }
        }
        
        // 외래 키 제약 조건 다시 활성화
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $count = DB::table('plans')->count();
        echo "✅ Plans 데이터를 성공적으로 import했습니다.\n";
        echo "📊 총 {$count}개의 플랜이 데이터베이스에 있습니다.\n";
        
    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        echo "❌ Import 중 오류가 발생했습니다: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function showHelp()
{
    echo "Plans 데이터 동기화 스크립트\n\n";
    echo "사용 방법:\n";
    echo "  Export (로컬): php sync_plans_to_server.php export\n";
    echo "  Import (서버): php sync_plans_to_server.php import\n\n";
    echo "단계:\n";
    echo "1. 로컬에서 'export' 명령어 실행\n";
    echo "2. 생성된 plans_export.sql 파일을 서버로 복사\n";
    echo "3. 서버에서 'import' 명령어 실행\n";
}

