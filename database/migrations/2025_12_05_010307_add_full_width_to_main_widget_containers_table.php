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
            $table->boolean('full_width')->default(false)->after('vertical_align');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn('full_width');
        });
    }
};
