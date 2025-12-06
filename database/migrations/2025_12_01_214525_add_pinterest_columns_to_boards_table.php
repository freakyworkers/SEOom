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
            // banned_words 컬럼이 있으면 그 뒤에, 없으면 다른 적절한 컬럼 뒤에 추가
            $afterColumn = null;
            if (Schema::hasColumn('boards', 'banned_words')) {
                $afterColumn = 'banned_words';
            } elseif (Schema::hasColumn('boards', 'qa_statuses')) {
                $afterColumn = 'qa_statuses';
            } elseif (Schema::hasColumn('boards', 'enable_comments')) {
                $afterColumn = 'enable_comments';
            } elseif (Schema::hasColumn('boards', 'enable_likes')) {
                $afterColumn = 'enable_likes';
            }
            
            if ($afterColumn) {
                $table->integer('pinterest_columns_mobile')->default(2)->nullable()->after($afterColumn)->comment('핀터레스트 타입 게시판 모바일 컬럼 수');
            } else {
                $table->integer('pinterest_columns_mobile')->default(2)->nullable()->comment('핀터레스트 타입 게시판 모바일 컬럼 수');
            }
            
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
