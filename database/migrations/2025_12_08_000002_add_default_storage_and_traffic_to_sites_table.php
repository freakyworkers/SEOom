<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // 기본 제공 스토리지/트래픽 설정 (기존 컬럼이 있으면 변경 없음)
            // 마이그레이션에서 기본값 설정은 SiteProvisionService에서 처리
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백 시 특별한 작업 없음
    }
};


