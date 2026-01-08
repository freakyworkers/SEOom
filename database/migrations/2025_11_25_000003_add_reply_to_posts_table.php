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
            if (!Schema::hasColumn('posts', 'reply_to')) {
                $table->unsignedBigInteger('reply_to')->nullable()->after('adopted_comment_id');
                $table->foreign('reply_to')->references('id')->on('posts')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'reply_to')) {
                $table->dropForeign(['reply_to']);
                $table->dropColumn('reply_to');
            }
        });
    }
};











