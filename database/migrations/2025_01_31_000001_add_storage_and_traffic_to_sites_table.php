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
            // 저장 용량 (MB 단위)
            $table->bigInteger('storage_used_mb')->default(0)->after('created_by')->comment('사용 중인 저장 용량 (MB)');
            $table->bigInteger('storage_limit_mb')->nullable()->after('storage_used_mb')->comment('저장 용량 제한 (MB)');
            
            // 트래픽 (MB 단위)
            $table->bigInteger('traffic_used_mb')->default(0)->after('storage_limit_mb')->comment('사용 중인 트래픽 (MB)');
            $table->bigInteger('traffic_limit_mb')->nullable()->after('traffic_used_mb')->comment('트래픽 제한 (MB)');
            $table->date('traffic_reset_date')->nullable()->after('traffic_limit_mb')->comment('트래픽 리셋일 (매월 초)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'storage_used_mb',
                'storage_limit_mb',
                'traffic_used_mb',
                'traffic_limit_mb',
                'traffic_reset_date',
            ]);
        });
    }
};



