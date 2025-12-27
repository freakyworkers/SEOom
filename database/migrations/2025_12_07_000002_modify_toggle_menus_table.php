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
        Schema::table('toggle_menus', function (Blueprint $table) {
            // 기존 title, content 컬럼 제거하고 name 추가
            $table->dropColumn(['title', 'content']);
            $table->string('name')->after('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toggle_menus', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('title')->after('site_id');
            $table->text('content')->after('title');
        });
    }
};




