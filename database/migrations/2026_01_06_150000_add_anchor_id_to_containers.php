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
        // main_widget_containers 테이블에 anchor_id 추가
        if (!Schema::hasColumn('main_widget_containers', 'anchor_id')) {
            Schema::table('main_widget_containers', function (Blueprint $table) {
                $table->string('anchor_id')->nullable()->after('order');
            });
        }

        // custom_page_widget_containers 테이블에 anchor_id 추가
        if (!Schema::hasColumn('custom_page_widget_containers', 'anchor_id')) {
            Schema::table('custom_page_widget_containers', function (Blueprint $table) {
                $table->string('anchor_id')->nullable()->after('order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn('anchor_id');
        });

        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn('anchor_id');
        });
    }
};

