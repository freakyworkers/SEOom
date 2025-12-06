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
        // 기존 사이트에 플랜에 맞는 트래픽 제한 적용
        $query = DB::table('sites')->whereNotNull('plan');
        
        // is_master_site 컬럼이 있으면 마스터 사이트 제외
        if (Schema::hasColumn('sites', 'is_master_site')) {
            $query->where('is_master_site', false);
        }
        
        $sites = $query->get();

        foreach ($sites as $site) {
            // 플랜 정보 가져오기
            $plan = DB::table('plans')
                ->where('slug', $site->plan)
                ->first();

            if ($plan && $plan->traffic_limit_mb) {
                // 사이트의 트래픽 제한 업데이트
                DB::table('sites')
                    ->where('id', $site->id)
                    ->update([
                        'traffic_limit_mb' => $plan->traffic_limit_mb,
                        'traffic_reset_date' => now()->startOfMonth()->toDateString(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 트래픽 제한 제거
        $query = DB::table('sites');
        
        // is_master_site 컬럼이 있으면 마스터 사이트 제외
        if (Schema::hasColumn('sites', 'is_master_site')) {
            $query->where('is_master_site', false);
        }
        
        $query->update([
            'traffic_limit_mb' => null,
            'traffic_reset_date' => null,
        ]);
    }
};


