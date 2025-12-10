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
            if (!Schema::hasColumn('boards', 'enable_share')) {
                // enable_admin_comment_adopt 컬럼이 있으면 그 뒤에, 없으면 맨 뒤에 추가
                if (Schema::hasColumn('boards', 'enable_admin_comment_adopt')) {
                    $table->boolean('enable_share')->default(true)->after('enable_admin_comment_adopt')->comment('게시글 공유 기능 활성화');
                } else {
                    $table->boolean('enable_share')->default(true)->comment('게시글 공유 기능 활성화');
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
            if (Schema::hasColumn('boards', 'enable_share')) {
                $table->dropColumn('enable_share');
            }
        });
    }
};

