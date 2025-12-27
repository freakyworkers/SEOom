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
            if (!Schema::hasColumn('boards', 'event_display_type')) {
                $table->string('event_display_type')->nullable()->after('type')->comment('이벤트 게시판 표시 타입: photo, general');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            if (Schema::hasColumn('boards', 'event_display_type')) {
                $table->dropColumn('event_display_type');
            }
        });
    }
};








