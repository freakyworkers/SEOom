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
        // 기존 'free' 플랜 삭제
        DB::table('plans')->where('slug', 'free')->delete();
        
        // 랜딩페이지 플랜 가격을 18000원으로 설정 (1회성 결제)
        DB::table('plans')->where('slug', 'landing')->update([
            'billing_type' => 'one_time',
            'one_time_price' => 18000,
            'price' => 0,
        ]);
        
        // 무료 플랜들의 제한사항을 통일 (모든 무료 플랜이 동일한 제한사항)
        $unifiedLimits = [
            'boards' => 2,
            'widgets' => 3,
            'custom_pages' => 2,
            'users' => 20,
            'storage' => 512, // 512MB
        ];
        $unifiedTrafficLimit = 2560; // 2.5GB
        
        // 무료 랜딩페이지 플랜 업데이트
        DB::table('plans')->where('slug', 'free-landing')->update([
            'limits' => json_encode($unifiedLimits),
            'traffic_limit_mb' => $unifiedTrafficLimit,
        ]);
        
        // 무료 브랜드 플랜 업데이트
        DB::table('plans')->where('slug', 'free-brand')->update([
            'limits' => json_encode($unifiedLimits),
            'traffic_limit_mb' => $unifiedTrafficLimit,
        ]);
        
        // 무료 커뮤니티 플랜 업데이트
        DB::table('plans')->where('slug', 'free-community')->update([
            'limits' => json_encode($unifiedLimits),
            'traffic_limit_mb' => $unifiedTrafficLimit,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 기본 free 플랜 복원 (선택적)
        // 랜딩페이지 플랜 가격 원복
        DB::table('plans')->where('slug', 'landing')->update([
            'billing_type' => 'free',
            'one_time_price' => null,
            'price' => 0,
        ]);
    }
};







