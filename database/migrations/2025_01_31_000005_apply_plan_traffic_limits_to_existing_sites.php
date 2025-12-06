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
        $sites = DB::table('sites')
            ->where('is_master_site', false)
            ->whereNotNull('plan')
            ->get();

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
        DB::table('sites')
            ->where('is_master_site', false)
            ->update([
                'traffic_limit_mb' => null,
                'traffic_reset_date' => null,
            ]);
    }
};


