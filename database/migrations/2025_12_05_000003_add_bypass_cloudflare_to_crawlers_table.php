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
        Schema::table('crawlers', function (Blueprint $table) {
            $table->boolean('bypass_cloudflare')->default(false)->after('is_active')->comment('Cloudflare/안티봇 우회 활성화');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crawlers', function (Blueprint $table) {
            $table->dropColumn('bypass_cloudflare');
        });
    }
};






