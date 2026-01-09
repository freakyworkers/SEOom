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
        // 유료 플랜들의 위젯 제한을 무제한(null)으로 설정
        $paidPlans = ['landing', 'brand', 'community'];
        
        foreach ($paidPlans as $slug) {
            $plan = DB::table('plans')->where('slug', $slug)->first();
            if ($plan) {
                $limits = json_decode($plan->limits, true) ?? [];
                
                // 유료 플랜은 위젯 제한 없음
                $limits['widgets'] = null;
                $limits['boards'] = null;
                $limits['custom_pages'] = null;
                $limits['users'] = null;
                // storage는 유지
                
                DB::table('plans')
                    ->where('slug', $slug)
                    ->update([
                        'limits' => json_encode($limits),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 무료 플랜의 제한사항으로 복원
        $freePlan = DB::table('plans')->where('slug', 'free-landing')->first();
        if ($freePlan) {
            $unifiedLimits = $freePlan->limits;
            
            $paidPlans = ['landing', 'brand', 'community'];
            foreach ($paidPlans as $slug) {
                DB::table('plans')
                    ->where('slug', $slug)
                    ->update([
                        'limits' => $unifiedLimits,
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};

