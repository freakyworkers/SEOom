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
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade')->unique();
            $table->text('notice')->nullable(); // 공지사항
            $table->boolean('auto_delete_24h')->default(false); // 24시간 후 자동 삭제
            $table->boolean('allow_guest')->default(false); // 비로그인 사용자 채팅 허용
            $table->text('banned_words')->nullable(); // 금지 단어 (줄바꿈으로 구분)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_settings');
    }
};



