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
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'is_pinned_top')) {
                $table->boolean('is_pinned_top')->default(false)->after('order');
            }
            if (!Schema::hasColumn('banners', 'pinned_position')) {
                $table->integer('pinned_position')->nullable()->after('is_pinned_top');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'is_pinned_top')) {
                $table->dropColumn('is_pinned_top');
            }
            if (Schema::hasColumn('banners', 'pinned_position')) {
                $table->dropColumn('pinned_position');
            }
        });
    }
};








