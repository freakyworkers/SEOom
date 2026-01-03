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
        // 랜딩페이지 플랜의 features 가져오기
        $landingPlan = DB::table('plans')->where('slug', 'landing')->first();
        if ($landingPlan) {
            // 무료 랜딩페이지 플랜의 features를 랜딩페이지 플랜과 동일하게 업데이트
            DB::table('plans')->where('slug', 'free-landing')->update([
                'features' => $landingPlan->features,
            ]);
        }
        
        // 브랜드 플랜의 features 가져오기
        $brandPlan = DB::table('plans')->where('slug', 'brand')->first();
        if ($brandPlan) {
            // 무료 브랜드 플랜의 features를 브랜드 플랜과 동일하게 업데이트
            DB::table('plans')->where('slug', 'free-brand')->update([
                'features' => $brandPlan->features,
            ]);
        }
        
        // 커뮤니티 플랜의 features 가져오기
        $communityPlan = DB::table('plans')->where('slug', 'community')->first();
        if ($communityPlan) {
            // 무료 커뮤니티 플랜의 features를 커뮤니티 플랜과 동일하게 업데이트
            DB::table('plans')->where('slug', 'free-community')->update([
                'features' => $communityPlan->features,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 특별한 작업 없음 (features는 원래대로 유지)
    }
};





