<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Site;
use App\Models\Map;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 기존 사이트에 기본 지도 추가
        $sites = Site::all();
        
        foreach ($sites as $site) {
            // 이미 지도가 있는 사이트는 건너뛰기
            if ($site->maps()->count() > 0) {
                continue;
            }
            
            $defaultMaps = [
                [
                    'name' => '구글 지도',
                    'map_type' => 'google',
                    'address' => '서울특별시 강남구 테헤란로 152',
                    'latitude' => 37.5013,
                    'longitude' => 127.0396,
                    'zoom' => 15,
                ],
                [
                    'name' => '카카오맵',
                    'map_type' => 'kakao',
                    'address' => '서울특별시 강남구 테헤란로 152',
                    'latitude' => 37.5013,
                    'longitude' => 127.0396,
                    'zoom' => 15,
                ],
                [
                    'name' => '네이버 지도',
                    'map_type' => 'naver',
                    'address' => '서울특별시 강남구 테헤란로 152',
                    'latitude' => 37.5013,
                    'longitude' => 127.0396,
                    'zoom' => 15,
                ],
            ];

            foreach ($defaultMaps as $mapData) {
                Map::create(array_merge($mapData, [
                    'site_id' => $site->id,
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 기본 지도 삭제 (선택사항)
        // 필요시 여기에 롤백 로직 추가
    }
};




