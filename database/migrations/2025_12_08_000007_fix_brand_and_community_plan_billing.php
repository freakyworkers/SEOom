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
        // 브랜드 플랜을 1회성 결제로 설정 (29000원)
        DB::table('plans')->where('slug', 'brand')->update([
            'billing_type' => 'one_time',
            'one_time_price' => 29000,
            'price' => 0,
        ]);
        
        // 커뮤니티 플랜을 1회성 결제로 설정 (59000원)
        DB::table('plans')->where('slug', 'community')->update([
            'billing_type' => 'one_time',
            'one_time_price' => 59000,
            'price' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 원래대로 복원
        DB::table('plans')->where('slug', 'brand')->update([
            'billing_type' => 'monthly',
            'one_time_price' => null,
            'price' => 29000,
        ]);
        
        DB::table('plans')->where('slug', 'community')->update([
            'billing_type' => 'monthly',
            'one_time_price' => null,
            'price' => 59000,
        ]);
    }
};





