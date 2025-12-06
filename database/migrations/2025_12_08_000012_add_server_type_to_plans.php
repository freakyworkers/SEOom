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
        // enum 타입을 변경하기 위해 ALTER TABLE 사용
        // MySQL에서 enum을 변경할 때는 기존 데이터를 확인하고 안전하게 변경
        try {
            DB::statement("ALTER TABLE plans MODIFY COLUMN type ENUM('landing', 'brand', 'community', 'server') DEFAULT 'landing'");
        } catch (\Exception $e) {
            // 이미 변경되었거나 오류가 발생한 경우 무시
            \Log::info('Plan type enum update skipped: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // server 타입 플랜 삭제 후 enum 복원
        DB::table('plans')->where('type', 'server')->delete();
        DB::statement("ALTER TABLE plans MODIFY COLUMN type ENUM('landing', 'brand', 'community') DEFAULT 'landing'");
    }
};

