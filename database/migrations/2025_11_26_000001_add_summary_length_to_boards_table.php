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
            if (!Schema::hasColumn('boards', 'summary_length')) {
                $table->integer('summary_length')->nullable()->default(150)->after('event_display_type')->comment('블로그 게시판 요약 내용 길이 (글자수)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            if (Schema::hasColumn('boards', 'summary_length')) {
                $table->dropColumn('summary_length');
            }
        });
    }
};









