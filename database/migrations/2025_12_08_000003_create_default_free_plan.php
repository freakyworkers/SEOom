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
        // 무료 플랜이 없으면 생성
        $freePlanExists = DB::table('plans')->where('slug', 'free')->exists();
        
        if (!$freePlanExists) {
            DB::table('plans')->insert([
                'name' => '무료 플랜',
                'slug' => 'free',
                'description' => '기본 기능을 무료로 사용할 수 있는 플랜입니다.',
                'type' => 'landing',
                'price' => 0,
                'billing_type' => 'free',
                'one_time_price' => null,
                'features' => json_encode([
                    'main_features' => ['dashboard', 'users', 'boards', 'posts'],
                    'board_types' => ['general'],
                    'registration_features' => [],
                    'sidebar_widget_types' => [],
                    'main_widget_types' => [],
                    'custom_page_widget_types' => [],
                ]),
                'limits' => json_encode([
                    'boards' => 3,
                    'widgets' => 0,
                    'custom_pages' => 0,
                    'users' => 10,
                    'storage' => 1024, // 1GB 기본 제공
                ]),
                'traffic_limit_mb' => 5120, // 5GB 기본 제공
                'sort_order' => 0,
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plans')->where('slug', 'free')->delete();
    }
};



