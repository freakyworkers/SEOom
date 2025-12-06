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
        Schema::table('boards', function (Blueprint $table) {
            if (!Schema::hasColumn('boards', 'enable_likes')) {
                // remove_links 컬럼이 있으면 그 뒤에, 없으면 테이블 끝에 추가
                if (Schema::hasColumn('boards', 'remove_links')) {
                    $table->boolean('enable_likes')->default(false)->after('remove_links')->comment('추천 기능 활성화');
                } else {
                    $table->boolean('enable_likes')->default(false)->comment('추천 기능 활성화');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn('enable_likes');
        });
    }
};



