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
            // 일반 설정
            $table->integer('max_posts_per_day')->default(0)->after('is_active');
            
            // SEO 설정
            $table->string('seo_title')->nullable()->after('max_posts_per_day');
            $table->text('seo_description')->nullable()->after('seo_title');
            
            // 등급 설정
            $table->enum('read_permission', ['guest', 'user', 'admin'])->default('guest')->after('seo_description');
            $table->enum('write_permission', ['guest', 'user', 'admin'])->default('user')->after('read_permission');
            $table->enum('comment_permission', ['guest', 'user', 'admin'])->default('user')->after('write_permission');
            
            // 포인트 설정
            $table->integer('read_points')->default(0)->after('comment_permission');
            $table->integer('write_points')->default(0)->after('read_points');
            $table->integer('delete_points')->default(0)->after('write_points');
            $table->integer('comment_points')->default(0)->after('delete_points');
            $table->integer('comment_delete_points')->default(0)->after('comment_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'max_posts_per_day',
                'seo_title',
                'seo_description',
                'read_permission',
                'write_permission',
                'comment_permission',
                'read_points',
                'write_points',
                'delete_points',
                'comment_points',
                'comment_delete_points',
            ]);
        });
    }
};
