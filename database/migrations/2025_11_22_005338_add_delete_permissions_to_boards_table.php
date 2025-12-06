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
            $table->enum('delete_permission', ['author', 'admin'])->default('author')->after('write_permission');
            $table->enum('comment_delete_permission', ['author', 'admin'])->default('author')->after('comment_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn(['delete_permission', 'comment_delete_permission']);
        });
    }
};
