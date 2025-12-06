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
            if (!Schema::hasColumn('banners', 'type')) {
                $table->string('type')->default('image')->after('location'); // 'image' or 'html'
            }
            if (!Schema::hasColumn('banners', 'html_code')) {
                $table->text('html_code')->nullable()->after('image_path');
            }
            // Make image_path nullable for HTML banners (only if column exists)
            if (Schema::hasColumn('banners', 'image_path')) {
                $table->string('image_path')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['type', 'html_code']);
            $table->string('image_path')->nullable(false)->change();
        });
    }
};

