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
        Schema::table('boards', function (Blueprint $table) {
            if (!Schema::hasColumn('boards', 'enable_comments')) {
                $table->boolean('enable_comments')->default(true)->after('enable_reply')->comment('댓글 기능 활성화');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            if (Schema::hasColumn('boards', 'enable_comments')) {
                $table->dropColumn('enable_comments');
            }
        });
    }
};





