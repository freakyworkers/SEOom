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
        // MainWidgetContainer에 상단/하단 여백 추가
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->integer('padding_top')->default(0)->after('widget_spacing');
            $table->integer('padding_bottom')->default(0)->after('padding_top');
        });

        // CustomPageWidgetContainer에 상단/하단 여백과 fixed_width_columns 추가
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->boolean('fixed_width_columns')->default(false)->after('full_width');
            $table->integer('padding_top')->default(0)->after('full_height');
            $table->integer('padding_bottom')->default(0)->after('padding_top');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['padding_top', 'padding_bottom']);
        });

        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['fixed_width_columns', 'padding_top', 'padding_bottom']);
        });
    }
};

