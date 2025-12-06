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
        if (!Schema::hasColumn('boards', 'saved_posts_enabled')) {
            Schema::table('boards', function (Blueprint $table) {
                // enable_likes 컬럼이 있으면 그 뒤에, 없으면 테이블 끝에 추가
                if (Schema::hasColumn('boards', 'enable_likes')) {
                    $table->boolean('saved_posts_enabled')->default(false)->after('enable_likes')->comment('저장하기 기능 활성화');
                } else {
                    $table->boolean('saved_posts_enabled')->default(false)->comment('저장하기 기능 활성화');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('boards', 'saved_posts_enabled')) {
            Schema::table('boards', function (Blueprint $table) {
                $table->dropColumn('saved_posts_enabled');
            });
        }
    }
};
