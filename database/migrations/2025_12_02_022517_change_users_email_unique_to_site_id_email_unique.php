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
        Schema::table('users', function (Blueprint $table) {
            // 기존 email unique 제약조건 제거
            $table->dropUnique(['email']);
            
            // site_id와 email의 조합을 unique로 설정
            $table->unique(['site_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // site_id와 email의 조합 unique 제약조건 제거
            $table->dropUnique(['site_id', 'email']);
            
            // email unique 제약조건 복원
            $table->unique('email');
        });
    }
};
