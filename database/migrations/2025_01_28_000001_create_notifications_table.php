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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'comment', 'message', 'point_award', 'point_exchange', 'event_application'
            $table->string('title'); // 알림 제목
            $table->text('content'); // 알림 내용
            $table->string('link')->nullable(); // 클릭 시 이동할 링크
            $table->json('data')->nullable(); // 추가 데이터 (게시글 ID, 댓글 ID 등)
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index(['site_id', 'user_id']);
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};





