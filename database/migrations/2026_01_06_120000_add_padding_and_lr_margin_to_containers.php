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
        // main_widget_containers 테이블
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->integer('margin_left')->default(0)->after('margin_bottom');
            $table->integer('margin_right')->default(0)->after('margin_left');
            $table->integer('padding_top')->default(0)->after('margin_right');
            $table->integer('padding_bottom')->default(0)->after('padding_top');
            $table->integer('padding_left')->default(0)->after('padding_bottom');
            $table->integer('padding_right')->default(0)->after('padding_left');
        });

        // custom_page_widget_containers 테이블
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->integer('margin_left')->default(0)->after('margin_bottom');
            $table->integer('margin_right')->default(0)->after('margin_left');
            $table->integer('padding_top')->default(0)->after('margin_right');
            $table->integer('padding_bottom')->default(0)->after('padding_top');
            $table->integer('padding_left')->default(0)->after('padding_bottom');
            $table->integer('padding_right')->default(0)->after('padding_left');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['margin_left', 'margin_right', 'padding_top', 'padding_bottom', 'padding_left', 'padding_right']);
        });

        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn(['margin_left', 'margin_right', 'padding_top', 'padding_bottom', 'padding_left', 'padding_right']);
        });
    }
};

