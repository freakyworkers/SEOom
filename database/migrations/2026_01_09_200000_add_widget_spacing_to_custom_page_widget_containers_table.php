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
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_page_widget_containers', 'widget_spacing')) {
                $table->integer('widget_spacing')->default(3)->after('full_height')->comment('위젯 간 간격 (Bootstrap mb 클래스 값: 0-5)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            if (Schema::hasColumn('custom_page_widget_containers', 'widget_spacing')) {
                $table->dropColumn('widget_spacing');
            }
        });
    }
};

