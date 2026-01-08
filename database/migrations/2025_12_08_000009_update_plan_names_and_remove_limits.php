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
        // 기본 플랜 이름을 "랜딩페이지 플랜"으로 변경
        DB::table('plans')->where('slug', 'landing')->update([
            'name' => '랜딩페이지 플랜',
        ]);
        
        // 유료 플랜들의 제한사항 제거 (boards, widgets, custom_pages, users를 null로)
        // storage와 traffic_limit_mb는 유지
        
        // 랜딩페이지 플랜 (유료)
        $landingPlan = DB::table('plans')->where('slug', 'landing')->first();
        if ($landingPlan) {
            $limits = json_decode($landingPlan->limits, true) ?? [];
            // 제한사항 제거 (boards, widgets, custom_pages, users)
            $limits['boards'] = null;
            $limits['widgets'] = null;
            $limits['custom_pages'] = null;
            $limits['users'] = null;
            // storage는 유지
            DB::table('plans')->where('slug', 'landing')->update([
                'limits' => json_encode($limits),
            ]);
        }
        
        // 브랜드 플랜 (유료)
        $brandPlan = DB::table('plans')->where('slug', 'brand')->first();
        if ($brandPlan) {
            $limits = json_decode($brandPlan->limits, true) ?? [];
            // 제한사항 제거
            $limits['boards'] = null;
            $limits['widgets'] = null;
            $limits['custom_pages'] = null;
            $limits['users'] = null;
            // storage는 유지
            DB::table('plans')->where('slug', 'brand')->update([
                'limits' => json_encode($limits),
            ]);
        }
        
        // 커뮤니티 플랜 (유료)
        $communityPlan = DB::table('plans')->where('slug', 'community')->first();
        if ($communityPlan) {
            $limits = json_decode($communityPlan->limits, true) ?? [];
            // 제한사항 제거 (이미 null일 수 있지만 확실히)
            $limits['boards'] = null;
            $limits['widgets'] = null;
            $limits['custom_pages'] = null;
            $limits['users'] = null;
            // storage는 유지
            DB::table('plans')->where('slug', 'community')->update([
                'limits' => json_encode($limits),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 원래 제한사항 복원
        DB::table('plans')->where('slug', 'landing')->update([
            'name' => '기본 플랜',
        ]);
        
        // 제한사항 복원은 롤백 시 수동으로 처리 필요
    }
};







