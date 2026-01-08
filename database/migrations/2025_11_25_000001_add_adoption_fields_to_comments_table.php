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
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'is_adopted')) {
                $table->boolean('is_adopted')->default(false)->after('content');
            }
            if (!Schema::hasColumn('comments', 'adoption_points')) {
                $table->integer('adoption_points')->default(0)->after('is_adopted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'is_adopted')) {
                $table->dropColumn('is_adopted');
            }
            if (Schema::hasColumn('comments', 'adoption_points')) {
                $table->dropColumn('adoption_points');
            }
        });
    }
};











