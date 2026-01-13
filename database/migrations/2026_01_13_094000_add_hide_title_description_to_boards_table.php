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
            if (!Schema::hasColumn('boards', 'hide_title_description')) {
                // enable_share 컬럼이 있으면 그 뒤에, 없으면 맨 뒤에 추가
                if (Schema::hasColumn('boards', 'enable_share')) {
                    $table->boolean('hide_title_description')->default(false)->after('enable_share')->comment('게시판 제목 및 설명 숨기기');
                } else {
                    $table->boolean('hide_title_description')->default(false)->comment('게시판 제목 및 설명 숨기기');
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
            if (Schema::hasColumn('boards', 'hide_title_description')) {
                $table->dropColumn('hide_title_description');
            }
        });
    }
};

