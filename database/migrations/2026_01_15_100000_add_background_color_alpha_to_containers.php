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
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->integer('background_color_alpha')->default(100)->after('background_color');
            $table->integer('background_image_alpha')->default(100)->after('background_image_url');
        });
        
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->integer('background_color_alpha')->default(100)->after('background_color');
            $table->integer('background_image_alpha')->default(100)->after('background_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['background_color_alpha', 'background_image_alpha']);
        });
        
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['background_color_alpha', 'background_image_alpha']);
        });
    }
};
