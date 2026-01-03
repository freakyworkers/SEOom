<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_exchange_settings', function (Blueprint $table) {
            $table->boolean('random_order')->default(false)->after('requirements');
            $table->integer('products_per_page')->default(12)->after('random_order');
        });
    }

    public function down(): void
    {
        Schema::table('point_exchange_settings', function (Blueprint $table) {
            $table->dropColumn(['random_order', 'products_per_page']);
        });
    }
};







