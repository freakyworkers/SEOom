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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_session_id')->nullable(); // 게스트 세션 ID
            $table->string('nickname'); // 닉네임 (로그인 사용자는 user.name, 게스트는 게스트1, 게스트2 등)
            $table->text('message'); // 채팅 내용
            $table->string('attachment_path')->nullable(); // 첨부파일 경로
            $table->string('attachment_type')->nullable(); // 첨부파일 타입 (image 등)
            $table->timestamps();
            
            $table->index(['site_id', 'created_at']);
            $table->index('guest_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};






