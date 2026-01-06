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
        // MainWidgetContainer에 상단/하단 마진 추가 (기본 하단 마진: 24px = Bootstrap mb-4)
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->integer('margin_top')->default(0)->after('widget_spacing');
            $table->integer('margin_bottom')->default(24)->after('margin_top');
        });

        // CustomPageWidgetContainer에 상단/하단 마진과 fixed_width_columns 추가
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->boolean('fixed_width_columns')->default(false)->after('full_width');
            $table->integer('margin_top')->default(0)->after('full_height');
            $table->integer('margin_bottom')->default(24)->after('margin_top');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['margin_top', 'margin_bottom']);
        });

        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['fixed_width_columns', 'margin_top', 'margin_bottom']);
        });
    }
};

