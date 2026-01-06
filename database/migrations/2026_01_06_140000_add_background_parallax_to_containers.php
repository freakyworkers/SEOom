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
            if (!Schema::hasColumn('main_widget_containers', 'background_parallax')) {
                $table->boolean('background_parallax')->default(false)->after('background_image_url');
            }
        });

        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_page_widget_containers', 'background_parallax')) {
                $table->boolean('background_parallax')->default(false)->after('background_image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            if (Schema::hasColumn('main_widget_containers', 'background_parallax')) {
                $table->dropColumn('background_parallax');
            }
        });

        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            if (Schema::hasColumn('custom_page_widget_containers', 'background_parallax')) {
                $table->dropColumn('background_parallax');
            }
        });
    }
};

