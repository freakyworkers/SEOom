<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_exchange_settings', function (Blueprint $table) {
            $table->integer('pc_columns')->default(4)->after('products_per_page');
            $table->integer('mobile_columns')->default(2)->after('pc_columns');
        });
    }

    public function down(): void
    {
        Schema::table('point_exchange_settings', function (Blueprint $table) {
            $table->dropColumn(['pc_columns', 'mobile_columns']);
        });
    }
};








