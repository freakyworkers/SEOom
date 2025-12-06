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
        // 서버 용량 플랜 정의 (트래픽 + 스토리지 병합)
        $serverPlans = [
            [
                'name' => '서버 용량 3만원',
                'slug' => 'server-30k',
                'description' => '트래픽 10GB + 스토리지 2GB 제공',
                'type' => 'server',
                'price' => 30000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]), // 서버 용량 플랜은 기능 없음
                'limits' => json_encode([
                    'storage' => 2048, // 2GB
                ]),
                'traffic_limit_mb' => 10240, // 10GB
                'sort_order' => 100,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '서버 용량 5만원',
                'slug' => 'server-50k',
                'description' => '트래픽 20GB + 스토리지 5GB 제공',
                'type' => 'server',
                'price' => 50000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]),
                'limits' => json_encode([
                    'storage' => 5120, // 5GB
                ]),
                'traffic_limit_mb' => 20480, // 20GB
                'sort_order' => 101,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '서버 용량 10만원',
                'slug' => 'server-100k',
                'description' => '트래픽 50GB + 스토리지 10GB 제공',
                'type' => 'server',
                'price' => 100000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]),
                'limits' => json_encode([
                    'storage' => 10240, // 10GB
                ]),
                'traffic_limit_mb' => 51200, // 50GB
                'sort_order' => 102,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '서버 용량 20만원',
                'slug' => 'server-200k',
                'description' => '트래픽 100GB + 스토리지 20GB 제공',
                'type' => 'server',
                'price' => 200000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]),
                'limits' => json_encode([
                    'storage' => 20480, // 20GB
                ]),
                'traffic_limit_mb' => 102400, // 100GB
                'sort_order' => 103,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '서버 용량 40만원',
                'slug' => 'server-400k',
                'description' => '트래픽 200GB + 스토리지 50GB 제공',
                'type' => 'server',
                'price' => 400000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]),
                'limits' => json_encode([
                    'storage' => 51200, // 50GB
                ]),
                'traffic_limit_mb' => 204800, // 200GB
                'sort_order' => 104,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '서버 용량 60만원',
                'slug' => 'server-600k',
                'description' => '트래픽 300GB + 스토리지 100GB 제공',
                'type' => 'server',
                'price' => 600000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]),
                'limits' => json_encode([
                    'storage' => 102400, // 100GB
                ]),
                'traffic_limit_mb' => 307200, // 300GB
                'sort_order' => 105,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '서버 용량 100만원',
                'slug' => 'server-1000k',
                'description' => '트래픽 500GB + 스토리지 200GB 제공',
                'type' => 'server',
                'price' => 1000000,
                'billing_type' => 'monthly',
                'one_time_price' => null,
                'features' => json_encode([]),
                'limits' => json_encode([
                    'storage' => 204800, // 200GB
                ]),
                'traffic_limit_mb' => 512000, // 500GB
                'sort_order' => 106,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($serverPlans as $planData) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plans')->where('type', 'server')->delete();
    }
};


