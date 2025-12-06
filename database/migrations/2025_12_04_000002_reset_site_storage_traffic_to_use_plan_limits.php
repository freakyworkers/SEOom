<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 서버 용량 플랜 구독 사이트를 제외한 모든 사이트의 storage_limit_mb와 traffic_limit_mb를 null로 설정
        // 이렇게 하면 플랜의 limits를 항상 사용하게 됨
        
        // 서버 용량 플랜 구독 사이트 찾기 (subscription의 plan이 server 타입인 경우)
        $serverCapacitySiteIds = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('plans.type', 'server')
            ->where('subscriptions.status', 'active')
            ->pluck('subscriptions.site_id')
            ->toArray();
        
        // 서버 용량 플랜 구독 사이트가 아닌 모든 사이트의 storage_limit_mb와 traffic_limit_mb를 null로 설정
        DB::table('sites')
            ->where('is_master_site', false)
            ->whereNotIn('id', $serverCapacitySiteIds)
            ->update([
                'storage_limit_mb' => null,
                'traffic_limit_mb' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 원래 값으로 복원하기 어려우므로 주석 처리
        // 필요시 각 사이트의 플랜에 맞는 기본값을 수동으로 설정해야 함
    }
};


