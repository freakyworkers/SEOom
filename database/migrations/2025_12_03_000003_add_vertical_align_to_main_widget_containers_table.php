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
            $table->string('vertical_align')->default('top')->after('columns'); // 상단, 중앙, 하단
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn('vertical_align');
        });
    }
};




