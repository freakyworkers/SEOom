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
            if (!Schema::hasColumn('posts', 'is_secret')) {
                $table->boolean('is_secret')->default(false)->after('is_pinned');
            }
            if (!Schema::hasColumn('posts', 'adoption_points')) {
                $table->integer('adoption_points')->default(0)->after('is_secret');
            }
            if (!Schema::hasColumn('posts', 'adopted_comment_id')) {
                $table->unsignedBigInteger('adopted_comment_id')->nullable()->after('adoption_points');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'is_secret')) {
                $table->dropColumn('is_secret');
            }
            if (Schema::hasColumn('posts', 'adoption_points')) {
                $table->dropColumn('adoption_points');
            }
            if (Schema::hasColumn('posts', 'adopted_comment_id')) {
                $table->dropColumn('adopted_comment_id');
            }
        });
    }
};









