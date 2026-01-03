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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('thumbnail_path');
            $table->string('code')->nullable()->after('site_name');
            $table->string('link')->nullable()->after('code');
            $table->json('bookmark_items')->nullable()->after('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'code', 'link', 'bookmark_items']);
        });
    }
};









