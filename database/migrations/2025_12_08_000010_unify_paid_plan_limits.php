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
        // 무료 플랜의 통일된 제한사항 가져오기
        $freePlan = DB::table('plans')->where('slug', 'free-landing')->first();
        if (!$freePlan) {
            // 무료 플랜이 없으면 기본값 사용
            $unifiedLimits = json_encode([
                'boards' => 2,
                'widgets' => 3,
                'custom_pages' => 2,
                'users' => 20,
                'storage' => 512, // 512MB
            ]);
            $unifiedTraffic = 2560; // 2.5GB
        } else {
            $unifiedLimits = $freePlan->limits;
            $unifiedTraffic = $freePlan->traffic_limit_mb;
        }
        
        // 유료 플랜들에 동일한 제한사항 적용
        $paidPlans = ['landing', 'brand', 'community'];
        
        foreach ($paidPlans as $slug) {
            DB::table('plans')
                ->where('slug', $slug)
                ->update([
                    'limits' => $unifiedLimits,
                    'traffic_limit_mb' => $unifiedTraffic,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 원래 제한사항 복원 (이전 상태로 되돌리기 어려우므로 주석 처리)
        // 각 플랜별로 원래 제한사항을 수동으로 복원해야 함
    }
};






