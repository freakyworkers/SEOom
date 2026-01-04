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
            // 로그인 타입: email (이메일로 로그인) 또는 username (아이디로 로그인)
            $table->string('login_type', 20)->default('email')->after('is_sample');
            
            // 테스트 어드민 계정 정보 (JSON 형태로 저장)
            // 구조: { "enabled": bool, "username": string, "password": string }
            $table->json('test_admin')->nullable()->after('login_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['login_type', 'test_admin']);
        });
    }
};

