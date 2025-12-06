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
            $table->integer('pinterest_columns_mobile')->default(2)->nullable()->after('banned_words')->comment('핀터레스트 타입 게시판 모바일 컬럼 수');
            $table->integer('pinterest_columns_tablet')->default(3)->nullable()->after('pinterest_columns_mobile')->comment('핀터레스트 타입 게시판 태블릿 컬럼 수');
            $table->integer('pinterest_columns_desktop')->default(4)->nullable()->after('pinterest_columns_tablet')->comment('핀터레스트 타입 게시판 데스크톱 컬럼 수');
            $table->integer('pinterest_columns_large')->default(6)->nullable()->after('pinterest_columns_desktop')->comment('핀터레스트 타입 게시판 큰 화면 컬럼 수');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'pinterest_columns_mobile',
                'pinterest_columns_tablet',
                'pinterest_columns_desktop',
                'pinterest_columns_large',
            ]);
        });
    }
};
