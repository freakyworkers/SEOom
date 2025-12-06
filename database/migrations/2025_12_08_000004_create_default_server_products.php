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
        // 서버 상품 가격 목록 (월간 결제)
        $serverPrices = [30000, 50000, 100000, 200000, 400000, 500000, 1000000];
        
        // 스토리지 상품 생성
        foreach ($serverPrices as $index => $price) {
            $storageAmount = $this->calculateStorageAmount($price);
            $slug = 'storage-' . ($price / 1000) . 'k';
            
            if (!DB::table('addon_products')->where('slug', $slug)->exists()) {
                DB::table('addon_products')->insert([
                    'name' => '추가 저장 용량 ' . $this->formatStorage($storageAmount),
                    'slug' => $slug,
                    'description' => '월간 ' . number_format($price) . '원으로 추가 저장 용량을 사용할 수 있습니다.',
                    'type' => 'storage',
                    'amount_mb' => $storageAmount,
                    'price' => $price,
                    'billing_cycle' => 'monthly',
                    'is_active' => true,
                    'sort_order' => $index * 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // 트래픽 상품 생성
        foreach ($serverPrices as $index => $price) {
            $trafficAmount = $this->calculateTrafficAmount($price);
            $slug = 'traffic-' . ($price / 1000) . 'k';
            
            if (!DB::table('addon_products')->where('slug', $slug)->exists()) {
                DB::table('addon_products')->insert([
                    'name' => '추가 트래픽 ' . $this->formatTraffic($trafficAmount),
                    'slug' => $slug,
                    'description' => '월간 ' . number_format($price) . '원으로 추가 트래픽을 사용할 수 있습니다.',
                    'type' => 'traffic',
                    'amount_mb' => $trafficAmount,
                    'price' => $price,
                    'billing_cycle' => 'monthly',
                    'is_active' => true,
                    'sort_order' => $index * 2 + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Calculate storage amount based on price.
     * 가격에 따라 스토리지 용량 계산 (예: 3만원 = 10GB, 5만원 = 20GB 등)
     */
    private function calculateStorageAmount(int $price): int
    {
        // 가격에 따른 스토리지 용량 (MB)
        $amounts = [
            30000 => 10240,   // 10GB
            50000 => 20480,   // 20GB
            100000 => 51200,  // 50GB
            200000 => 102400, // 100GB
            400000 => 204800, // 200GB
            500000 => 307200, // 300GB
            1000000 => 614400, // 600GB
        ];
        
        return $amounts[$price] ?? ($price / 3); // 기본값: 가격의 1/3 MB
    }

    /**
     * Calculate traffic amount based on price.
     * 가격에 따라 트래픽 용량 계산
     */
    private function calculateTrafficAmount(int $price): int
    {
        // 가격에 따른 트래픽 용량 (MB)
        $amounts = [
            30000 => 51200,   // 50GB
            50000 => 102400,  // 100GB
            100000 => 204800, // 200GB
            200000 => 409600, // 400GB
            400000 => 819200, // 800GB
            500000 => 1024000, // 1TB
            1000000 => 2048000, // 2TB
        ];
        
        return $amounts[$price] ?? ($price * 2); // 기본값: 가격의 2배 MB
    }

    /**
     * Format storage amount for display.
     */
    private function formatStorage(int $mb): string
    {
        if ($mb >= 1024) {
            return round($mb / 1024, 1) . 'GB';
        }
        return $mb . 'MB';
    }

    /**
     * Format traffic amount for display.
     */
    private function formatTraffic(int $mb): string
    {
        if ($mb >= 1024) {
            return round($mb / 1024, 1) . 'GB';
        }
        return $mb . 'MB';
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 서버 상품 삭제
        $serverPrices = [30000, 50000, 100000, 200000, 400000, 500000, 1000000];
        
        foreach ($serverPrices as $price) {
            $storageSlug = 'storage-' . ($price / 1000) . 'k';
            $trafficSlug = 'traffic-' . ($price / 1000) . 'k';
            
            DB::table('addon_products')->where('slug', $storageSlug)->delete();
            DB::table('addon_products')->where('slug', $trafficSlug)->delete();
        }
    }
};


